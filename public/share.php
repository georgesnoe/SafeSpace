<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $mood = trim($_POST['mood'] ?? '');

    if ($content === '') {
        $message = 'Le message est obligatoire.';
    } else {
        $status = isToxic($content) ? 'blocked' : 'published';
        if ($pseudo === '') {
            $pseudo = randomPseudo();
        }

        $stmt = $pdo->prepare('INSERT INTO posts (pseudo, content, mood, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$pseudo, $content, $mood !== '' ? $mood : null, $status]);

        if ($status === 'blocked') {
            $message = 'Ton message a été bloqué automatiquement pour préserver un espace bienveillant.';
        } else {
            header('Location: feed.php');
            exit;
        }
    }
}
?>
<section class="hero page-hero">
  <h1>Share Your Thoughts</h1>
  <p class="muted">Exprime ce que tu traverses. Ton identité reste protégée.</p>
</section>

<?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>

<section class="card soft-card" style="margin-top: 1rem;">
  <form method="post" action="share.php">
    <div class="field"><label>Pseudo (optionnel)</label><input name="pseudo" placeholder="Anonyme-1234" /></div>
    <div class="field">
      <label>Humeur (optionnel)</label>
      <select name="mood">
        <option value="">Sélectionner</option>
        <option>Stress</option>
        <option>Espoir</option>
        <option>Fatigue</option>
        <option>Gratitude</option>
      </select>
    </div>
    <div class="field"><label>Ton message</label><textarea name="content" required></textarea></div>
    <button class="btn" type="submit">Publier anonymement</button>
  </form>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
