<?php

require_once __DIR__ . '/app.php';

function randomPseudo(): string
{
    return 'Anonyme-' . random_int(1000, 9999);
}

function isToxic(string $text): bool
{
    $bannedWords = [
        'idiot', 'stupide', 'debile', 'débile', 'nul', 'hate', 'suicide',
        'connard', 'connasse', 'pute', 'fdp', 'moron', 'kill yourself'
    ];

    $normalized = mb_strtolower($text);
    foreach ($bannedWords as $word) {
        if (str_contains($normalized, $word)) {
            return true;
        }
    }

    return false;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatCFA($amount): string
{
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

function appBaseUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';
    return $scheme . '://' . $host;
}

function makeAbsoluteUrl(string $path): string
{
    return rtrim(appBaseUrl(), '/') . '/' . ltrim($path, '/');
}

function cinetpayConfigured(): bool
{
    return CINETPAY_SITE_ID !== '' && CINETPAY_API_KEY !== '';
}

function cinetpayRequest(array $payload): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('L' . "'" . 'extension cURL est requise pour le paiement.');
    }

    $ch = curl_init('https://api-checkout.cinetpay.com/v2/payment');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 30,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Erreur CinetPay: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Reponse CinetPay invalide.');
    }

    if ($status >= 400) {
        throw new RuntimeException('CinetPay a retourne une erreur HTTP.');
    }

    return $decoded;
}

function cinetpayCheckTransaction(string $transactionId): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('L' . "'" . 'extension cURL est requise pour le paiement.');
    }

    $payload = [
        'apikey' => CINETPAY_API_KEY,
        'site_id' => CINETPAY_SITE_ID,
        'transaction_id' => $transactionId,
    ];

    $ch = curl_init('https://api-checkout.cinetpay.com/v2/payment/check');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 30,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Erreur CinetPay: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Reponse CinetPay invalide.');
    }

    if ($status >= 400) {
        throw new RuntimeException('CinetPay a retourne une erreur HTTP.');
    }

    return $decoded;
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function premiumExpiryInPast(?string $expiresAt): bool
{
    if (!$expiresAt) {
        return false;
    }

    $ts = strtotime($expiresAt);
    return $ts !== false && $ts <= time();
}

function isPremiumActive(?array $user): bool
{
    if (!$user) {
        return false;
    }

    if (($user['role'] ?? '') === 'admin') {
        return true;
    }

    $expiresAt = $user['premium_expires_at'] ?? null;
    if ($expiresAt && premiumExpiryInPast((string)$expiresAt)) {
        return false;
    }

    return (int)($user['is_premium'] ?? 0) === 1;
}

function nextPremiumExpiry(?string $expiresAt = null): string
{
    $base = ($expiresAt && !premiumExpiryInPast($expiresAt))
        ? new DateTimeImmutable($expiresAt)
        : new DateTimeImmutable('now');

    return $base->modify('+1 month')->format('Y-m-d H:i:s');
}

function getAdminEmail(): string
{
    if (file_exists(ADMIN_CONFIG_FILE)) {
        $value = trim((string)file_get_contents(ADMIN_CONFIG_FILE));
        if ($value !== '') {
            return strtolower($value);
        }
    }
    return strtolower(DEFAULT_ADMIN_EMAIL);
}

function setAdminEmail(string $email): void
{
    file_put_contents(ADMIN_CONFIG_FILE, strtolower(trim($email)));
}

function bootstrapAdminFromCurrentUser(): void
{
    global $pdo;

    if (!isset($pdo) || !$pdo instanceof PDO) {
        return;
    }

    $user = currentUser();
    if (!$user || file_exists(ADMIN_CONFIG_FILE)) {
        return;
    }

    $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE lower(role) = 'admin'")->fetchColumn();
    if ($adminCount > 0) {
        return;
    }

    setAdminEmail((string)$user['email']);
    $stmt = $pdo->prepare('UPDATE users SET role = "admin" WHERE id = ?');
    $stmt->execute([(int)$user['id']]);
    $_SESSION['user']['role'] = 'admin';
}

function syncCurrentUserState(PDO $pdo): void
{
    $user = currentUser();
    if (!$user) {
        return;
    }

    $stmt = $pdo->prepare('SELECT id, role, is_premium, premium_expires_at FROM users WHERE id = ?');
    $stmt->execute([(int)$user['id']]);
    $fresh = $stmt->fetch();
    if (!$fresh) {
        return;
    }

    $premiumActive = ((int)$fresh['is_premium'] === 1) && !premiumExpiryInPast($fresh['premium_expires_at'] ?? null);
    if (!$premiumActive && (int)$fresh['is_premium'] === 1) {
        $pdo->prepare('UPDATE users SET is_premium = 0 WHERE id = ?')->execute([(int)$fresh['id']]);
        $fresh['is_premium'] = 0;
    }

    $_SESSION['user']['role'] = $fresh['role'];
    $_SESSION['user']['is_premium'] = $premiumActive ? 1 : 0;
    $_SESSION['user']['premium_expires_at'] = $fresh['premium_expires_at'];
}

function bootstrapAdminIfNeeded(PDO $pdo, array $user): array
{
    if (!$user) {
        return $user;
    }

    if (file_exists(ADMIN_CONFIG_FILE)) {
        return $user;
    }

    $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE lower(role) = 'admin'")->fetchColumn();
    if ($adminCount > 0) {
        return $user;
    }

    setAdminEmail((string)$user['email']);
    $stmt = $pdo->prepare('UPDATE users SET role = "admin" WHERE id = ?');
    $stmt->execute([(int)$user['id']]);
    $user['role'] = 'admin';

    return $user;
}

function isAdmin(?array $user): bool
{
    if (!$user) {
        return false;
    }

    return ($user['role'] ?? 'member') === 'admin' || strtolower((string)$user['email']) === getAdminEmail();
}

function requireAuth(): void
{
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void
{
    bootstrapAdminFromCurrentUser();
    $user = currentUser();
    if (!isAdmin($user)) {
        header('Location: index.php');
        exit;
    }
}
