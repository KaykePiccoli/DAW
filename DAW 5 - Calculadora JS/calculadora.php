<?php
// define q o arquivo vai funcionar em json
header('Content-Type: application/json');

// recebe tudo enviado pelo js
$json_recebido = file_get_contents('php://input');

// decodifica o json em php
$dados = json_decode($json_recebido, true);

// teste se recebeu corretamente
if ($dados) {
    $num1 = isset($dados['num1']) ? (float)$dados['num1'] : 0;
    $num2 = isset($dados['num2']) ? (float)$dados['num2'] : 0;
    $operacao = isset($dados['operacao']) ? $dados['operacao'] : '';
    
    $resultado = null;
    $erro = null;

    // fazz os calculos
    switch ($operacao) {
        case 'soma':
            $resultado = $num1 + $num2;
            break;
        case 'subtracao':
            $resultado = $num1 - $num2;
            break;
        case 'multiplicacao':
            $resultado = $num1 * $num2;
            break;
        case 'divisao':
            if ($num2 == 0) {
                $erro = "Erro: nao eh possivel dividir por zero";
            } else {
                $resultado = $num1 / $num2;
            }
            break;
        default:
            $erro = "Erro";
    }

    // retorna a resposta, se eh resultado correto ou erro
    $resposta = [
        'resultado' => $resultado,
        'erro' => $erro
    ];

    // converte em json 
    echo json_encode($resposta);
} else {
    // se n receber json, cai aqui
    echo json_encode(['erro' => 'Nenhum dado recebido.']);
}
?>