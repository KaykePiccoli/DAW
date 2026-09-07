<?php
require_once "funcoes.php";

$alunos = ler_alunos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Alunos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; margin-right: 5px; cursor: pointer; border: none; border-radius: 4px; color: #fff; }
        .btn-alterar { background-color: #2196F3; }
        .btn-excluir { background-color: #e53935; }
    </style>
</head>
<body>

<h1>Lista de Alunos</h1>

<table>
    <thead>
        <tr>
            <th>Matrícula</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($alunos) > 0): ?>
            <?php foreach ($alunos as $aluno): ?>
                <tr>
                    <td><?php echo htmlspecialchars($aluno['matricula']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['email']); ?></td>
                    <td>
                        <!-- Botão Alterar: envia a matricula via GET -->
                        <form action="mostrar_aluno_alterar.php" method="GET" style="display:inline;">
                            <input type="hidden" name="matricula" value="<?php echo htmlspecialchars($aluno['matricula']); ?>">
                            <button type="submit" class="btn btn-alterar">Alterar</button>
                        </form>

                        <!-- Botão Excluir: envia a matricula via GET -->
                        <form action="mostrar_aluno_excluir.php" method="GET" style="display:inline;">
                            <input type="hidden" name="matricula" value="<?php echo htmlspecialchars($aluno['matricula']); ?>">
                            <button type="submit" class="btn btn-excluir">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Nenhum aluno cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>