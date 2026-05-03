<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$sqlitePath = database_path('database.sqlite');
if (! is_file($sqlitePath)) {
    fwrite(STDERR, "SQLite database not found: {$sqlitePath}\n");
    exit(1);
}

$sqlite = new PDO('sqlite:' . $sqlitePath);
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mysql = DB::connection('mysql');
$mysqlPdo = $mysql->getPdo();
$mysqlPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = $sqlite
    ->query("select name from sqlite_master where type = 'table' and name not like 'sqlite_%' order by name")
    ->fetchAll(PDO::FETCH_COLUMN);

$mysql->statement('SET FOREIGN_KEY_CHECKS=0');

try {
    foreach ($tables as $table) {
        if (! Schema::connection('mysql')->hasTable($table)) {
            echo "SKIP {$table}: missing on MySQL\n";
            continue;
        }

        $mysql->table($table)->truncate();
    }

    foreach ($tables as $table) {
        if (! Schema::connection('mysql')->hasTable($table)) {
            continue;
        }

        $sqliteColumns = array_map(
            fn ($column) => $column['name'],
            $sqlite->query('PRAGMA table_info("' . str_replace('"', '""', $table) . '")')->fetchAll(PDO::FETCH_ASSOC)
        );

        $mysqlColumns = array_map(
            fn ($column) => $column->Field,
            $mysql->select('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`')
        );

        $columns = array_values(array_intersect($sqliteColumns, $mysqlColumns));
        if ($columns === []) {
            echo "SKIP {$table}: no common columns\n";
            continue;
        }

        $quotedSqliteColumns = array_map(
            fn ($column) => '"' . str_replace('"', '""', $column) . '"',
            $columns
        );
        $rows = $sqlite
            ->query('SELECT ' . implode(', ', $quotedSqliteColumns) . ' FROM "' . str_replace('"', '""', $table) . '"')
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach (array_chunk($rows, 500) as $chunk) {
            $mysql->table($table)->insert($chunk);
        }

        echo "COPIED {$table}: " . count($rows) . "\n";
    }

    foreach ($tables as $table) {
        if (! Schema::connection('mysql')->hasTable($table)) {
            continue;
        }

        $autoColumn = collect($mysql->select('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`'))
            ->first(fn ($column) => str_contains((string) $column->Extra, 'auto_increment'));

        if (! $autoColumn) {
            continue;
        }

        $field = $autoColumn->Field;
        $nextId = (int) ($mysql->table($table)->max($field) ?? 0) + 1;
        $mysql->statement('ALTER TABLE `' . str_replace('`', '``', $table) . "` AUTO_INCREMENT = {$nextId}");
    }
} finally {
    $mysql->statement('SET FOREIGN_KEY_CHECKS=1');
}

echo "DONE\n";
