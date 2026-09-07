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
    <title>Excluir Aluno</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .dados { margin-bottom: 20px; }
        .dados p { margin: 4px 0; }
        .btn-confirmar { padding: 8px 16px; background-color: #e53935; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .btn-cancelar { padding: 8px 16px; background-color: #999; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>

<h1>Confirmar Exclusão de Aluno</h1>

<div class="dados">
    <p><strong>Matrícula:</strong> <?php echo htmlspecialchars($aluno['matricula']); ?></p>
    <p><strong>Nome:</strong> <?php echo htmlspecialchars($aluno['nome']); ?></p>
    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($aluno['email']); ?></p>
</div>

<p>Tem certeza que deseja excluir este aluno? Esta ação não pode ser desfeita.</p>

<form action="excluir_aluno.php" method="POST">
    <input type="hidden" name="matricula" value="<?php echo htmlspecialchars($aluno['matricula']); ?>">
    <button type="submit" class="btn-confirmar">Confirmar Exclusão</button>
    <a href="listar_alunos.php" class="btn-cancelar">Cancelar</a>
</form>

</body>
</html>