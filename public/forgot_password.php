<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$message = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email invalide.';
    } else {
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE lower(email) = ?');
        $stmt->execute([$email]);
        $dbUser = $stmt->fetch();

        if ($dbUser) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

            $pdo->prepare('DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL')->execute([(int)$dbUser['id']]);
            $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
            $stmt->execute([(int)$dbUser['id'], $tokenHash, $expiresAt]);

            $resetLink = 'reset_password.php?token=' . urlencode($token);
            $message = 'Un lien de reinitialisation a ete genere.';
        } else {
            $message = 'Si un compte existe, un lien de reinitialisation peut etre genere.';
        }
    }
}
?>
<section class="card auth-panel">
  <h1>Mot de passe oublié</h1>
  <p class="muted">Entre ton email pour generer un nouveau mot de passe.</p>
  <?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>
  <?php if ($resetLink !== ''): ?>
    <div class="alert">
      <p style="margin-top:0;">Lien de reinitialisation:</p>
      <p style="word-break:break-all; margin-bottom:0;"><a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a></p>
    </div>
  <?php endif; ?>
  <form method="post">
    <div class="field"><label>Email</label><input type="email" name="email" required /></div>
    <button class="btn" type="submit">Generer le lien</button>
  </form>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
