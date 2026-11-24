<?php

$pdo = new PDO(
    'sqlite:' . __DIR__ . '/database/disciplinas.sqlite'
);

$pdo->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);

$sql = file_get_contents(__DIR__ . '/database/schema.sql');

$pdo->exec($sql);
