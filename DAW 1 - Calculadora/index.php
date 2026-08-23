<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Calculadora DAW</title>
</head>
<body>

    <h1>Calculadora</h1>

    <form method="POST">
        <label>Primeiro número:</label><br>
        <input type="number" step="any" name="numero1" required><br><br>

        <label>Segundo número:</label><br>
        <input type="number" step="any" name="numero2" required><br><br>

        <label>Operação Desejada:</label><br>
        <select name="operacao" required>
            <option value="">Selecione</option>
            <option value="somar">Adição (+)</option>
            <option value="subtrair">Subtração (-)</option>
            <option value="multiplicar">Multiplicação (x)</option>
            <option value="dividir">Divisão (/)</option>
        </select><br><br>

        <button type="submit">Calcular</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $numero1 = $_POST["numero1"];
        $numero2 = $_POST["numero2"];
        $operacao = $_POST["operacao"];

        switch ($operacao) {
            case "somar":
                $resultado = $numero1 + $numero2;
                break;

            case "subtrair":
                $resultado = $numero1 - $numero2;
                break;

            case "multiplicar":
                $resultado = $numero1 * $numero2;
                break;

            case "dividir":
                if ($numero2 == 0) {
                    $resultado = "Erro: Divisão por zero!";
                } else {
                    $resultado = $numero1 / $numero2;
                }
                break;
        }

        echo "<h3>Resultado: $resultado</h3>";
    }
    ?>

</body>
</html>