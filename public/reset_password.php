<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$message = '';
$done = false;
$tokenHash = $token !== '' ? hash('sha256', $token) : '';

$resetRow = null;
if ($tokenHash !== '') {
    $stmt = $pdo->prepare(
        'SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at, u.email
         FROM password_resets pr
         JOIN users u ON u.id = pr.user_id
         WHERE pr.token_hash = ?
         ORDER BY pr.id DESC
         LIMIT 1'
    );
    $stmt->execute([$tokenHash]);
    $resetRow = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['password_confirm'] ?? '');

    if (!$resetRow) {
        $message = 'Lien invalide ou expiré.';
    } elseif ($resetRow['used_at'] !== null) {
        $message = 'Ce lien a deja ete utilise.';
    } elseif (strtotime((string)$resetRow['expires_at']) < time()) {
        $message = 'Ce lien a expire.';
    } elseif (strlen($password) < 8) {
        $message = 'Le mot de passe doit contenir au moins 8 caracteres.';
    } elseif ($password !== $confirm) {
        $message = 'Les mots de passe ne correspondent pas.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, (int)$resetRow['user_id']]);
            $pdo->prepare('UPDATE password_resets SET used_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([(int)$resetRow['id']]);
            $pdo->prepare('DELETE FROM password_resets WHERE user_id = ? AND id != ?')->execute([(int)$resetRow['user_id'], (int)$resetRow['id']]);
            $pdo->commit();
            $done = true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $message = 'Impossible de mettre a jour le mot de passe.';
        }
    }
}
?>
<section class="card auth-panel">
  <h1>Reinitialiser le mot de passe</h1>
  <?php if ($done): ?>
    <div class="alert">Mot de passe mis a jour. Tu peux maintenant te connecter.</div>
    <p><a href="login.php">Aller a la connexion</a></p>
  <?php else: ?>
    <?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>
    <?php if (!$resetRow && $token !== ''): ?>
      <div class="alert">Lien invalide ou expiré.</div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="token" value="<?= e($token) ?>" />
      <div class="field"><label>Nouveau mot de passe</label><input type="password" name="password" required /></div>
      <div class="field"><label>Confirmer le mot de passe</label><input type="password" name="password_confirm" required /></div>
      <button class="btn" type="submit">Changer le mot de passe</button>
    </form>
  <?php endif; ?>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
