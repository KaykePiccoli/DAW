
<?php

// O banco é criado na primeira execução, a partir do schema.sql.

function conectar(): PDO {
    $pasta = __DIR__ . '/database';

    if (!is_dir($pasta)) {
        mkdir($pasta, 0775, true);
    }

    $novo = !file_exists($pasta . '/perguntas.sqlite');

    $pdo = new PDO('sqlite:' . $pasta . '/perguntas.sqlite');

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('PRAGMA foreign_keys = ON');

    // CREATE TABLE IF NOT EXISTS permite recuperar um banco criado parcialmente.
    $pdo->exec(file_get_contents($pasta . '/schema.sql'));

    return $pdo;
}
