<?php
declare(strict_types=1);

$databaseConfig = [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/../storage/database.sqlite',
];

$pdo = new PDO(
    $databaseConfig['driver'] . ':' . $databaseConfig['database']
);

echo 'Migrating database...', PHP_EOL;

$checkinsSql = <<<SQLite3
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

$pdo->exec($checkinsSql);

$accountsSql = <<<SQLite3
    CREATE TABLE IF NOT EXISTS accounts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uuid TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL,
        handler_id TEXT NOT NULL
    );
SQLite3;

$pdo->exec($accountsSql);

echo 'Done.', PHP_EOL;