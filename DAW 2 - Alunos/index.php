<?php
$msg = ""; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome = $_POST["nome"];
    $matricula = $_POST["matricula"];
    $curso = $_POST["curso"];

    if (!file_exists("alunos.txt")) {
        $arqAluno = fopen("alunos.txt", "w") or die("erro ao criar arquivo");
        $linha = "nome;matricula;curso\n";
        fwrite($arqAluno, $linha);
        fclose($arqAluno);
    }

    // Abre o arquivo em modo append para adicionar no final
    $arqAluno = fopen("alunos.txt", "a") or die("erro ao criar arquivo");
    $linha = $nome . ";" . $matricula . ";" . $curso . "\n";
    fwrite($arqAluno, $linha);
    fclose($arqAluno);
    
    $msg = "Aluna(o) cadastrado.";
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Aluno</title>
    <link rel="stylesheet" href="estilo.css">
</head>

<body>
    <div class="container">
        <h1>Cadastro de Aluno</h1>
        <p class="subtitulo">
            Preencha os dados do aluno
        </p>

        <form action="" method="POST">
            <label for="nome">Nome:</label>
            <input
                type="text"
                name="nome"
                id="nome"
                required
            >
            
            <label for="matricula">Matricula:</label>
            <input
                type="text"
                name="matricula"
                id="matricula"
                required
            >
            
            <label for="curso">Curso:</label>
            <input
                type="text"
                name="curso"
                id="curso"
                required
            >
            
            <input
                type="submit"
                value="Cadastrar Aluno"
            >
        </form>

        <?php if (!empty($msg)) { ?>
            <p class="mensagem">
                <?php echo $msg; ?>
            </p>
        <?php } ?>
    </div>
</body>
</html>