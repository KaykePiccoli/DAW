<?php

require_once "funcoes.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

$matricula = $_POST['matricula'] ?? '';
$nome      = trim($_POST['nome'] ?? '');
$email     = trim($_POST['email'] ?? '');

if ($matricula === '' || $nome === '' || $email === '') {
    die("Todos os campos são obrigatórios.");
}

$alunos = ler_alunos();

if (!isset($alunos[$matricula])) {
    die("Aluno não encontrado.");
}

$alunos[$matricula]['nome']  = $nome;
$alunos[$matricula]['email'] = $email;

salvar_alunos($alunos);

header("Location: listar_alunos.php");
exit;