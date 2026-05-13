<?php
require_once __DIR__ . '/../config/db.php';

$sqlFile = __DIR__ . '/../sql/schema_sqlite.sql';

if (!file_exists($sqlFile)) {
    exit('Fichier schema_sqlite.sql introuvable.');
}

try {
    $schema = file_get_contents($sqlFile);
    if ($schema === false) {
        throw new RuntimeException('Lecture du schema impossible.');
    }

    $pdo->exec($schema);

    $columns = $pdo->query("PRAGMA table_info(motivation_posts)")->fetchAll();
    $hasPublished = false;
    foreach ($columns as $col) {
        if (($col['name'] ?? '') === 'is_published') {
            $hasPublished = true;
            break;
        }
    }
    if (!$hasPublished) {
        $pdo->exec("ALTER TABLE motivation_posts ADD COLUMN is_published INTEGER NOT NULL DEFAULT 0");
    }

    echo 'Setup SQLite termine. Tu peux ouvrir index.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Erreur setup: ' . $e->getMessage();
}
