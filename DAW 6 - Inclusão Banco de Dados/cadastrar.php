
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sigla = strtoupper(trim($_POST['sigla'] ?? ''));
    $nome = trim($_POST['nome'] ?? '');

    $carga_horaria = filter_var(
        $_POST['carga_horaria'] ?? '',
        FILTER_VALIDATE_INT
    );

    if ($sigla === '' || $nome === '' || 
        $carga_horaria === false || $carga_horaria <= 0) {

        echo "Preencha os campos corretamente.";

    } else {

        try {

            require_once 'conexao.php';

            $sql = "INSERT INTO disciplinas 
                    (sigla, nome, carga_horaria)
                    VALUES (?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $sigla,
                $nome,
                $carga_horaria
            ]);

            echo "Disciplina cadastrada com sucesso!";

        } catch (PDOException $e) {

            echo "Não foi possível cadastrar a disciplina.
                  Verifique se a sigla já existe.";

        }
    }
}

?>

<br><br>
<a href="index.html">Voltar ao cadastro</a>
