<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$stmt = $pdo->query(
    "SELECT p.id, p.pseudo, p.content, p.mood, p.created_at,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id AND c.status='published') AS comment_count
     FROM posts p
     WHERE p.status='published'
     ORDER BY p.created_at DESC"
);
$posts = $stmt->fetchAll();
?>
<section class="hero page-hero">
  <h1>Supportive Community</h1>
  <p class="muted">Publications anonymes de la communauté.</p>
</section>
<section class="post-list">
  <?php if (!$posts): ?>
    <article class="card soft-card"><p>Aucune publication pour le moment.</p></article>
  <?php endif; ?>

  <?php foreach ($posts as $post): ?>
    <article class="card soft-card">
      <div class="row">
        <strong><?= e($post['pseudo']) ?></strong>
        <?php if (!empty($post['mood'])): ?><span class="chip"><?= e($post['mood']) ?></span><?php endif; ?>
      </div>
      <p><?= nl2br(e($post['content'])) ?></p>
      <div class="row">
        <a href="post.php?id=<?= (int)$post['id'] ?>">Voir et commenter</a>
        <span class="muted"><?= (int)$post['comment_count'] ?> commentaire(s)</span>
      </div>
    </article>
  <?php endforeach; ?>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
