
<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexao.php';

// FUNÇÕES AUXILIARES

function responder($valor, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($valor, JSON_UNESCAPED_UNICODE);
    exit;
}

function obrigatorio($valor) {
    if (!is_string($valor) || trim($valor) === '') {
        responder(['erro' => 'Preencha todos os campos obrigatórios.'], 400);
    }

    return trim($valor);
}

function executar($db, $sql, $valores = []) {
    $stmt = $db->prepare($sql);
    $stmt->execute($valores);
    return $stmt;
}

// IDENTIFICAÇÃO DO TIPO DE REGISTRO

$tipo = $_GET['tipo'] ?? '';

$tabelas = [
    'usuarios' => 'usuarios',
    'me' => 'perguntas_me',
    'texto' => 'perguntas_texto'
];

if (!isset($tabelas[$tipo])) {
    responder(['erro' => 'Tipo inválido.'], 400);
}

$tabela = $tabelas[$tipo];

$metodo = $_SERVER['REQUEST_METHOD'];

if (!in_array($metodo, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    responder(['erro' => 'Método não permitido.'], 405);
}

try {
    $db = conectar();

    // GET - CONSULTAR REGISTROS

    if ($metodo === 'GET') {
        $registros = executar(
            $db,
            "SELECT * FROM $tabela ORDER BY id"
        )->fetchAll();

        if ($tipo !== 'usuarios') {
            foreach ($registros as &$p) {
                if ($tipo === 'texto') {
                    $r = executar(
                        $db,
                        'SELECT gabarito FROM respostas_texto WHERE pergunta_id = ?',
                        [$p['id']]
                    )->fetch();

                    $p['gabarito'] = $r['gabarito'] ?? '';

                } else {
                    $opcoes = executar(
                        $db,
                        'SELECT posicao, texto, correta FROM respostas_me WHERE pergunta_id = ? ORDER BY posicao',
                        [$p['id']]
                    )->fetchAll();

                    $p['opcoes'] = array_map(
                        fn($o) => [
                            'posicao' => (int)$o['posicao'],
                            'texto' => $o['texto'],
                            'correta' => (bool)$o['correta']
                        ],
                        $opcoes
                    );
                }
            }

            unset($p);
        }

        responder($registros);
    }

    // RECEBIMENTO DOS DADOS JSON

    $entrada = json_decode(file_get_contents('php://input'), true);

    if (!is_array($entrada)) {
        responder(['erro' => 'JSON inválido.'], 400);
    }

    $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT);

    // VERIFICAÇÃO DO REGISTRO

    if ($metodo !== 'POST') {
        if (
            !$id ||
            $id < 1 ||
            !executar(
                $db,
                "SELECT id FROM $tabela WHERE id = ?",
                [$id]
            )->fetch()
        ) {
            responder(['erro' => 'Registro não encontrado.'], 404);
        }
    }

    // DELETE - EXCLUIR

    if ($metodo === 'DELETE') {
        executar(
            $db,
            "DELETE FROM $tabela WHERE id = ?",
            [$id]
        );

        responder(['sucesso' => true]);
    }

    // VALIDAÇÃO DOS CAMPOS

    if ($tipo === 'usuarios') {
        $nome = obrigatorio($entrada['nome'] ?? null);
        $email = obrigatorio($entrada['email'] ?? null);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            responder(['erro' => 'E-mail inválido.'], 400);
        }

    } else {
        $enunciado = obrigatorio($entrada['enunciado'] ?? null);

        if ($tipo === 'texto') {
            $gabarito = obrigatorio($entrada['gabarito'] ?? null);

        } else {
            $opcoes = $entrada['opcoes'] ?? null;
            $correta = $entrada['correta'] ?? null;

            if (
                !is_array($opcoes) ||
                count($opcoes) !== 4 ||
                !is_int($correta) ||
                $correta < 0 ||
                $correta > 3
            ) {
                responder([
                    'erro' => 'Informe quatro campos e uma alternativa correta.'
                ], 400);
            }

            foreach ($opcoes as $opcao) {
                if (!is_string($opcao)) {
                    responder(['erro' => 'Alternativa inválida.'], 400);
                }
            }

            $opcoes = array_map('trim', $opcoes);

            if (
                count(array_filter($opcoes, fn($o) => $o !== '')) < 2 ||
                $opcoes[$correta] === ''
            ) {
                responder([
                    'erro' => 'Preencha pelo menos duas alternativas, incluindo a correta.'
                ], 400);
            }
        }
    }

    // INÍCIO DA TRANSAÇÃO

    $db->beginTransaction();

    // CADASTRAR OU ATUALIZAR USUÁRIO

    if ($tipo === 'usuarios') {
        if ($metodo === 'POST') {
            executar(
                $db,
                'INSERT INTO usuarios (nome, email) VALUES (?, ?)',
                [$nome, $email]
            );
        } else {
            executar(
                $db,
                'UPDATE usuarios SET nome = ?, email = ? WHERE id = ?',
                [$nome, $email, $id]
            );
        }

    } else {
        // CADASTRAR OU ATUALIZAR PERGUNTA

        if ($metodo === 'POST') {
            executar(
                $db,
                "INSERT INTO $tabela (enunciado) VALUES (?)",
                [$enunciado]
            );

            $id = (int)$db->lastInsertId();

        } else {
            executar(
                $db,
                "UPDATE $tabela SET enunciado = ? WHERE id = ?",
                [$enunciado, $id]
            );
        }

        // RESPOSTA DISSERTATIVA

        if ($tipo === 'texto') {
            executar(
                $db,
                'INSERT INTO respostas_texto (pergunta_id, gabarito)
                 VALUES (?, ?)
                 ON CONFLICT(pergunta_id)
                 DO UPDATE SET gabarito = excluded.gabarito',
                [$id, $gabarito]
            );

        } else {
            // RESPOSTAS DE MÚLTIPLA ESCOLHA

            executar(
                $db,
                'DELETE FROM respostas_me WHERE pergunta_id = ?',
                [$id]
            );

            foreach ($opcoes as $posicao => $texto) {
                if ($texto === '') continue;

                executar(
                    $db,
                    'INSERT INTO respostas_me
                     (pergunta_id, posicao, texto, correta)
                     VALUES (?, ?, ?, ?)',
                    [
                        $id,
                        $posicao,
                        $texto,
                        (int)($posicao === $correta)
                    ]
                );
            }
        }
    }

    if ($tipo === 'usuarios' && $metodo === 'POST') {
        $id = (int)$db->lastInsertId();
    }

    // CONFIRMAÇÃO DA TRANSAÇÃO

    $db->commit();

    responder([
        'sucesso' => true,
        'id' => $id
    ], $metodo === 'POST' ? 201 : 200);

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log($e->getMessage());

    responder([
        'erro' => 'Erro interno. Verifique o PHP e a extensão pdo_sqlite.'
    ], 500);
}
