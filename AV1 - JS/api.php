<?php
// API simples: recebe JSON do JavaScript e grava arquivos TXT.
header('Content-Type: application/json; charset=utf-8');

// RESPOSTA JSON

function responder($valor, $status = 200) {
    http_response_code($status);
    echo json_encode($valor, JSON_UNESCAPED_UNICODE);
    exit;
}

// MANIPULAÇÃO DOS ARQUIVOS

function arquivo($nome) {
    return __DIR__ . '/dados/' . $nome . '.txt';
}

function ler($nome) {
    $caminho = arquivo($nome);

    if (!file_exists($caminho)) return [];

    $linhas = file(
        $caminho,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    return array_map(
        fn($linha) => explode('|', $linha),
        $linhas ?: []
    );
}

function gravar($nome, $registros) {
    $linhas = array_map(
        fn($r) => implode('|', $r),
        $registros
    );

    $conteudo = $linhas ? implode("\n", $linhas) . "\n" : '';

    if (file_put_contents(arquivo($nome), $conteudo, LOCK_EX) === false) {
        responder(['erro' => 'Não foi possível gravar os dados.'], 500);
    }
}

function proximoId($registros) {
    return $registros
        ? max(array_map(fn($r) => (int)$r[0], $registros)) + 1
        : 1;
}

function campo($valor) {
    $texto = trim((string)($valor ?? ''));

    if ($texto === '' || strpbrk($texto, "|\r\n") !== false) {
        responder([
            'erro' => 'Preencha os campos sem usar | ou quebras de linha.'
        ], 400);
    }

    return $texto;
}

function removerId($registros, $id) {
    return array_values(array_filter(
        $registros,
        fn($r) => (int)$r[0] !== $id
    ));
}

// TIPOS DE REGISTROS

$tipo = $_GET['tipo'] ?? '';

$arquivos = [
    'usuarios' => ['usuarios'],
    'me' => ['perguntas_me', 'respostas_me'],
    'texto' => ['perguntas_texto', 'respostas_texto']
];

if (!isset($arquivos[$tipo])) {
    responder(['erro' => 'Tipo inválido.'], 400);
}

[$nomePerguntas, $nomeRespostas] = array_pad(
    $arquivos[$tipo], 2, null
);

$perguntas = ler($nomePerguntas);
$respostas = $nomeRespostas ? ler($nomeRespostas) : [];
$metodo = $_SERVER['REQUEST_METHOD'];

// GET - LISTAR

if ($metodo === 'GET') {
    $lista = [];

    foreach ($perguntas as $p) {
        $id = (int)$p[0];

        if ($tipo === 'usuarios') {
            $lista[] = [
                'id' => $id,
                'nome' => $p[1],
                'email' => $p[2]
            ];
            continue;
        }

        $registro = [
            'id' => $id,
            'enunciado' => $p[1]
        ];

        $vinculadas = array_values(array_filter(
            $respostas,
            fn($r) => (int)$r[1] === $id
        ));

        if ($tipo === 'texto') {
            $registro['gabarito'] = $vinculadas[0][2] ?? '';
        } else {
            $registro['opcoes'] = array_map(
                fn($r) => [
                    'texto' => $r[2],
                    'correta' => $r[3] === '1'
                ],
                $vinculadas
            );
        }

        $lista[] = $registro;
    }

    responder($lista);
}

// VALIDAÇÃO DO MÉTODO

if (!in_array($metodo, ['POST', 'PUT', 'DELETE'], true)) {
    responder(['erro' => 'Método inválido.'], 405);
}

$entrada = json_decode(file_get_contents('php://input'), true);

if (!is_array($entrada)) {
    responder(['erro' => 'JSON inválido.'], 400);
}

$id = (int)($entrada['id'] ?? 0);

// LOCALIZAR REGISTRO PARA EDIÇÃO OU EXCLUSÃO

if ($metodo !== 'POST') {
    if ($id < 1 || !array_filter(
        $perguntas,
        fn($p) => (int)$p[0] === $id
    )) {
        responder(['erro' => 'Registro não encontrado.'], 404);
    }

    $perguntas = removerId($perguntas, $id);

    if ($nomeRespostas) {
        $respostas = array_values(array_filter(
            $respostas,
            fn($r) => (int)$r[1] !== $id
        ));
    }
}

// DELETE - EXCLUIR

if ($metodo === 'DELETE') {
    gravar($nomePerguntas, $perguntas);

    if ($nomeRespostas) {
        gravar($nomeRespostas, $respostas);
    }

    responder(['sucesso' => true]);
}

// POST - CADASTRAR / PUT - ATUALIZAR

if ($metodo === 'POST') {
    $id = proximoId($perguntas);
}

if ($tipo === 'usuarios') {
    $novo = [
        $id,
        campo($entrada['nome'] ?? null),
        campo($entrada['email'] ?? null)
    ];

    if (!filter_var($novo[2], FILTER_VALIDATE_EMAIL)) {
        responder(['erro' => 'E-mail inválido.'], 400);
    }

} else {
    $novo = [
        $id,
        campo($entrada['enunciado'] ?? null)
    ];

    if ($tipo === 'texto') {
        $respostas[] = [
            proximoId($respostas),
            $id,
            campo($entrada['gabarito'] ?? null)
        ];

    } else {
        $opcoes = $entrada['opcoes'] ?? [];
        $correta = $entrada['correta'] ?? -1;

        if (
            !is_array($opcoes) ||
            count($opcoes) !== 4 ||
            !is_numeric($correta) ||
            (int)$correta < 0 ||
            (int)$correta > 3 ||
            count(array_filter(
                $opcoes,
                fn($o) => trim((string)$o) !== ''
            )) < 2 ||
            trim((string)$opcoes[(int)$correta]) === ''
        ) {
            responder([
                'erro' => 'Preencha ao menos duas opções e marque uma opção preenchida como correta.'
            ], 400);
        }

        $idResposta = proximoId($respostas);

        foreach ($opcoes as $indice => $opcao) {
            if (trim((string)$opcao) === '') continue;

            $respostas[] = [
                $idResposta++,
                $id,
                campo($opcao),
                (int)($indice === (int)$correta)
            ];
        }
    }
}

// GRAVAÇÃO FINAL

$perguntas[] = $novo;

gravar($nomePerguntas, $perguntas);

if ($nomeRespostas) {
    gravar($nomeRespostas, $respostas);
}

responder([
    'sucesso' => true,
    'id' => $id
], $metodo === 'POST' ? 201 : 200);
