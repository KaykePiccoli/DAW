<?php
require_once "funcoes.php";

if (!isset($_GET['matricula']) || $_GET['matricula'] === '') {
    die("Matrícula não informada.");
}

$matricula = $_GET['matricula'];
$aluno = buscar_aluno($matricula);

if ($aluno === null) {
    die("Aluno não encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Aluno</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type=text], input[type=email] { width: 300px; padding: 6px; margin-top: 4px; }
        .btn { margin-top: 20px; padding: 8px 16px; background-color: #2196F3; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<h1>Alterar Aluno</h1>

<form action="alterar_aluno.php" method="POST">
    <label>Matrícula</label>
    <input type="text" name="matricula" value="<?php echo htmlspecialchars($aluno['matricula']); ?>" readonly>

    <label>Nome</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($aluno['nome']); ?>" required>

    <label>E-mail</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($aluno['email']); ?>" required>

    <br>
    <button type="submit" class="btn">Salvar Alterações</button>
</form>

<p><a href="listar_alunos.php">Voltar para a lista</a></p>

</body>
</html>