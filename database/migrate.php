<?php
declare(strict_types=1);

$databaseConfig = [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/../storage/database.sqlite',
];

$pdo = new PDO(
    $databaseConfig['driver'] . ':' . $databaseConfig['database']
);

var_dump("Migrating database...");

$sql = <<<SQLite3
    CREATE TABLE IF NOT EXISTS checkins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uuid TEXT NOT NULL UNIQUE,
        handler TEXT NOT NULL,
        peed BOOLEAN DEFAULT FALSE,
        pooped BOOLEAN DEFAULT FALSE,
        eaten BOOLEAN DEFAULT FALSE,
        created_at DATETIME NOT NULL
    );
SQLite3;

$pdo->exec($sql);