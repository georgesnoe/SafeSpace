<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);
    exit('OK');
}

$transactionId = trim((string)($_POST['cpm_trans_id'] ?? $_POST['transaction_id'] ?? ''));
$siteId = trim((string)($_POST['cpm_site_id'] ?? $_POST['site_id'] ?? ''));

if ($transactionId === '' || $siteId === '') {
    http_response_code(200);
    exit('OK');
}

if ($siteId !== CINETPAY_SITE_ID) {
    http_response_code(200);
    exit('OK');
}

$stmt = $pdo->prepare('SELECT * FROM payments WHERE transaction_id = ? LIMIT 1');
$stmt->execute([$transactionId]);
$payment = $stmt->fetch();

if (!$payment) {
    http_response_code(200);
    exit('OK');
}

try {
    $verification = cinetpayCheckTransaction($transactionId);
    $data = $verification['data'] ?? [];
    $status = strtoupper((string)($data['status'] ?? ''));

    $pdo->beginTransaction();
    if ($status === 'ACCEPTED' || ((string)($verification['code'] ?? '') === '00' && $status === 'ACCEPTED')) {
        $stmt = $pdo->prepare('SELECT premium_expires_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$payment['user_id']]);
        $currentExpiry = (string)($stmt->fetchColumn() ?: '');
        $newExpiry = nextPremiumExpiry($currentExpiry);

        $pdo->prepare('UPDATE payments SET status = ?, provider_response = ?, paid_at = COALESCE(paid_at, CURRENT_TIMESTAMP), expires_at = ?, updated_at = CURRENT_TIMESTAMP WHERE transaction_id = ?')
            ->execute(['paid', json_encode($verification, JSON_UNESCAPED_UNICODE), $newExpiry, $transactionId]);

        $pdo->prepare('UPDATE users SET is_premium = 1, premium_expires_at = ? WHERE id = ?')
            ->execute([$newExpiry, (int)$payment['user_id']]);
    } elseif ($status === 'REFUSED' || (string)($verification['code'] ?? '') === '627') {
        $pdo->prepare('UPDATE payments SET status = ?, provider_response = ?, updated_at = CURRENT_TIMESTAMP WHERE transaction_id = ?')
            ->execute(['failed', json_encode($verification, JSON_UNESCAPED_UNICODE), $transactionId]);
    } else {
        $pdo->prepare('UPDATE payments SET provider_response = ?, updated_at = CURRENT_TIMESTAMP WHERE transaction_id = ?')
            ->execute([json_encode($verification, JSON_UNESCAPED_UNICODE), $transactionId]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

http_response_code(200);
echo 'OK';
