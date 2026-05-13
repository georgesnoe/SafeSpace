<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$postId = (int)($_GET['id'] ?? 0);
if ($postId < 1) {
    exit('Publication invalide.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'comment') {
        $pseudo = trim($_POST['pseudo'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($content !== '') {
            if ($pseudo === '') {
                $pseudo = randomPseudo();
            }
            $status = isToxic($content) ? 'blocked' : 'published';
            $stmt = $pdo->prepare('INSERT INTO comments (post_id, pseudo, content, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$postId, $pseudo, $content, $status]);
            if ($status === 'blocked') {
                $message = 'Commentaire bloqué automatiquement.';
            } else {
                header('Location: post.php?id=' . $postId);
                exit;
            }
        }
    }

    if (($_POST['action'] ?? '') === 'report') {
        $targetType = trim($_POST['target_type'] ?? 'post');
        $targetId = (int)($_POST['target_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($targetId > 0) {
            $stmt = $pdo->prepare('INSERT INTO reports (target_type, target_id, reason) VALUES (?, ?, ?)');
            $stmt->execute([$targetType, $targetId, $reason !== '' ? $reason : null]);
            $message = 'Signalement envoyé.';
        }
    }
}

$stmt = $pdo->prepare("SELECT id, pseudo, content, mood, created_at FROM posts WHERE id=? AND status='published'");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    exit('Publication introuvable.');
}

$stmt = $pdo->prepare("SELECT id, pseudo, content, created_at FROM comments WHERE post_id=? AND status='published' ORDER BY created_at ASC");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll();
?>
<article class="hero page-hero">
  <h1>Détail de publication</h1>
  <p><strong><?= e($post['pseudo']) ?>:</strong> <?= nl2br(e($post['content'])) ?></p>
  <?php if (!empty($post['mood'])): ?><span class="chip"><?= e($post['mood']) ?></span><?php endif; ?>
</article>

<?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>

<section class="card soft-card" style="margin-top: 1rem;">
  <h2>Commentaires de soutien</h2>
  <?php if (!$comments): ?><p class="muted">Pas encore de commentaire.</p><?php endif; ?>
  <?php foreach ($comments as $comment): ?>
    <article style="padding: .7rem 0; border-bottom: 1px solid var(--outline);">
      <strong><?= e($comment['pseudo']) ?></strong>
      <p><?= nl2br(e($comment['content'])) ?></p>
      <form method="post" action="post.php?id=<?= $postId ?>" class="row">
        <input type="hidden" name="action" value="report" />
        <input type="hidden" name="target_type" value="comment" />
        <input type="hidden" name="target_id" value="<?= (int)$comment['id'] ?>" />
        <input name="reason" placeholder="Raison (optionnel)" />
        <button class="btn btn-soft" type="submit">Signaler</button>
      </form>
    </article>
  <?php endforeach; ?>
</section>

<section class="card soft-card" style="margin-top: 1rem;">
  <h2>Ajouter un commentaire</h2>
  <form method="post" action="post.php?id=<?= $postId ?>">
    <input type="hidden" name="action" value="comment" />
    <div class="field"><label>Pseudo (optionnel)</label><input name="pseudo" placeholder="Anonyme-XXXX" /></div>
    <div class="field"><label>Message</label><textarea name="content" required></textarea></div>
    <button class="btn" type="submit">Publier</button>
  </form>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
