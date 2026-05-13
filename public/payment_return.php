<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$transactionId = trim((string)($_GET['transaction_id'] ?? $_POST['transaction_id'] ?? ''));
$payment = null;
if ($transactionId !== '') {
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE transaction_id = ? LIMIT 1');
    $stmt->execute([$transactionId]);
    $payment = $stmt->fetch();
}
?>

<section class="card auth-panel">
  <h1>Retour paiement</h1>
  <?php if (!$transactionId): ?>
    <div class="alert">Aucune transaction fournie.</div>
  <?php elseif (!$payment): ?>
    <div class="alert">Transaction introuvable.</div>
  <?php else: ?>
    <p>Transaction: <strong><?= e($payment['transaction_id']) ?></strong></p>
    <p>Statut actuel: <strong><?= e((string)$payment['status']) ?></strong></p>
    <p class="muted">Cette page ne valide rien elle-même. La confirmation finale passe par la notification serveur.</p>
  <?php endif; ?>
  <p><a href="pricing.php">Retour aux offres</a></p>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
