<?php
// Funções p ler e gravar os alunos no arquivo texto

define('ARQUIVO_ALUNOS', __DIR__ . '/alunos.txt');
define('SEPARADOR', '|');

function ler_alunos() {
    $alunos = [];

    if (!file_exists(ARQUIVO_ALUNOS)) {
        return $alunos;
    }

    $linhas = file(ARQUIVO_ALUNOS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $campos = explode(SEPARADOR, $linha);

        if (count($campos) === 3) {
            $matricula = $campos[0];
            $alunos[$matricula] = [
                'matricula' => $matricula,
                'nome'      => $campos[1],
                'email'     => $campos[2],
            ];
        }
    }

    return $alunos;
}

function salvar_alunos($alunos) {
    $linhas = [];

    foreach ($alunos as $aluno) {
        $linhas[] = $aluno['matricula'] . SEPARADOR . $aluno['nome'] . SEPARADOR . $aluno['email'];
    }

    $conteudo = implode(PHP_EOL, $linhas) . PHP_EOL;

    file_put_contents(ARQUIVO_ALUNOS, $conteudo);
}

function buscar_aluno($matricula) {
    $alunos = ler_alunos();
    return $alunos[$matricula] ?? null;
}