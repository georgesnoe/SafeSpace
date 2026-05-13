<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$conversationKey = 'general-safe-room';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderPseudo = trim($_POST['sender_pseudo'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($content !== '') {
        if ($senderPseudo === '') {
            $senderPseudo = randomPseudo();
        }

        $status = isToxic($content) ? 'blocked' : 'published';
        $stmt = $pdo->prepare('INSERT INTO private_messages (conversation_key, sender_pseudo, content, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$conversationKey, $senderPseudo, $content, $status]);

        if ($status === 'blocked') {
            $message = 'Message privé bloqué automatiquement.';
        } else {
            header('Location: private_space.php');
            exit;
        }
    }
}

$stmt = $pdo->prepare("SELECT sender_pseudo, content, created_at FROM private_messages WHERE conversation_key=? AND status='published' ORDER BY created_at ASC LIMIT 100");
$stmt->execute([$conversationKey]);
$messages = $stmt->fetchAll();
?>
<section class="hero page-hero">
  <h1>My Private Space</h1>
  <p class="muted">Canal privé modéré automatiquement.</p>
</section>

<?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>

<section class="card soft-card" style="margin-top:1rem;">
  <h2>Conversation</h2>
  <?php if (!$messages): ?><p class="muted">Aucun message pour le moment.</p><?php endif; ?>
  <?php foreach ($messages as $m): ?>
    <article style="padding: .7rem 0; border-bottom: 1px solid var(--outline);">
      <strong><?= e($m['sender_pseudo']) ?></strong>
      <p><?= nl2br(e($m['content'])) ?></p>
    </article>
  <?php endforeach; ?>
</section>

<section class="card soft-card" style="margin-top:1rem;">
  <h2>Envoyer un message</h2>
  <form method="post" action="private_space.php">
    <div class="field"><label>Pseudo (optionnel)</label><input name="sender_pseudo" /></div>
    <div class="field"><label>Message</label><textarea name="content" required></textarea></div>
    <button class="btn" type="submit">Envoyer</button>
  </form>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
