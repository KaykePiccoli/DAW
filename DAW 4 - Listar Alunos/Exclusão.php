<?php

require_once "funcoes.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

$matricula = $_POST['matricula'] ?? '';

if ($matricula === '') {
    die("Matrícula não informada.");
}

$alunos = ler_alunos();

if (!isset($alunos[$matricula])) {
    die("Aluno não encontrado.");
}

unset($alunos[$matricula]);

salvar_alunos($alunos);

header("Location: listar_alunos.php");
exit;