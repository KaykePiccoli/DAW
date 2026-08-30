<?php

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $siglaExcluir = $_POST["sigla"];

    if (file_exists("disciplinas.txt")) {

        $arqDisc = fopen("disciplinas.txt", "r") or die("Erro ao abrir arquivo");

        $disciplinas = [];
        $excluida = false;

        // Lê todas as linhas do arquivo
        while (($linha = fgets($arqDisc)) !== false) {

            $dados = explode(";", trim($linha));

            // Mantém o cabeçalho
            if ($dados[0] == "nome") {
                $disciplinas[] = $linha;
                continue;
            }

            // Verifica a sigla
            if ($dados[1] == $siglaExcluir) {
                $excluida = true;
            } else {
                $disciplinas[] = $linha;
            }
        }

        fclose($arqDisc);

        // Reescreve o arquivo sem a disciplina excluída
        $arqDisc = fopen("disciplinas.txt", "w") or die("Erro ao abrir arquivo");

        foreach ($disciplinas as $linha) {
            fwrite($arqDisc, $linha);
        }

        fclose($arqDisc);

        if ($excluida) {
            $msg = "Disciplina excluída com sucesso!";
        } else {
            $msg = "Disciplina não encontrada.";
        }

    } else {
        $msg = "O arquivo de disciplinas não existe.";
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Excluir Disciplina</title>
</head>

<body>

    <h1>Excluir Disciplina</h1>

    <form action="ExcluirDisciplina.php" method="POST">

        Sigla da disciplina:
        <input type="text" name="sigla">

        <br><br>

        <input type="submit" value="Excluir Disciplina">

    </form>

    <p>
        <?php echo $msg; ?>
    </p>

    <h2>Disciplinas cadastradas</h2>

    <?php

    if (file_exists("disciplinas.txt")) {

        $arqDisc = fopen("disciplinas.txt", "r");

        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>Nome</th>";
        echo "<th>Sigla</th>";
        echo "<th>Carga Horária</th>";
        echo "</tr>";

        // Ignora o cabeçalho
        fgets($arqDisc);

        while (($linha = fgets($arqDisc)) !== false) {

            $dados = explode(";", trim($linha));

            echo "<tr>";
            echo "<td>" . $dados[0] . "</td>";
            echo "<td>" . $dados[1] . "</td>";
            echo "<td>" . $dados[2] . "</td>";
            echo "</tr>";
        }

        echo "</table>";

        fclose($arqDisc);

    } else {

        echo "<p>Nenhuma disciplina cadastrada.</p>";
    }

    ?>

</body>

</html>
```
