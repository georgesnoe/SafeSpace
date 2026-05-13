<?php

declare(strict_types=1);

$databasePath = __DIR__ . '/../database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $columns = $pdo->query('PRAGMA table_info(users)')->fetchAll();
    $hasPremiumExpiry = false;
    foreach ($columns as $column) {
        if (($column['name'] ?? '') === 'premium_expires_at') {
            $hasPremiumExpiry = true;
            break;
        }
    }
    if (!$hasPremiumExpiry) {
        $pdo->exec('ALTER TABLE users ADD COLUMN premium_expires_at DATETIME NULL');
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS password_resets (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      token_hash TEXT NOT NULL,
      expires_at DATETIME NOT NULL,
      used_at DATETIME NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS payments (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      provider TEXT NOT NULL,
      transaction_id TEXT NOT NULL UNIQUE,
      amount INTEGER NOT NULL,
      currency TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT "pending",
      payment_url TEXT NULL,
      payment_token TEXT NULL,
      provider_response TEXT NULL,
      paid_at DATETIME NULL,
      expires_at DATETIME NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_password_resets_user_id ON password_resets(user_id)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_password_resets_token_hash ON password_resets(token_hash)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_payments_user_id ON payments(user_id)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status)');
} catch (PDOException $e) {
    exit('Erreur de connexion SQLite: ' . $e->getMessage());
}
