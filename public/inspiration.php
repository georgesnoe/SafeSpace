<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$quotes = $pdo->query('SELECT id, quote, author, category FROM inspirations ORDER BY created_at DESC LIMIT 30')->fetchAll();

$isPremium = isPremiumActive($user);
if ($isPremium) {
    $stmt = $pdo->query("SELECT m.title, m.content, m.category, m.tier, u.name AS author
                         FROM motivation_posts m
                         JOIN users u ON u.id = m.user_id
                         WHERE m.is_published = 1
                         ORDER BY m.created_at DESC LIMIT 30");
} else {
    $stmt = $pdo->query("SELECT m.title, m.content, m.category, m.tier, u.name AS author
                         FROM motivation_posts m
                         JOIN users u ON u.id = m.user_id
                         WHERE m.is_published = 1 AND m.tier = 'free'
                         ORDER BY m.created_at DESC LIMIT 30");
}
$creatorPosts = $stmt->fetchAll();
?>
<section class="hero page-hero inspiration-hero">
  <h1>Daily Inspiration</h1>
  <p class="muted">Des messages courts pour t'encourager chaque jour.</p>
</section>

<section class="grid grid-3" style="margin-top: 1rem;">
  <?php foreach ($quotes as $q): ?>
    <article class="card soft-card">
      <p>"<?= e($q['quote']) ?>"</p>
      <p class="muted">- <?= e($q['author'] ?: 'Anonyme') ?></p>
      <?php if (!empty($q['category'])): ?><span class="chip"><?= e($q['category']) ?></span><?php endif; ?>
    </article>
  <?php endforeach; ?>

  <?php foreach ($creatorPosts as $p): ?>
    <article class="card soft-card">
      <h3><?= e($p['title']) ?> <span class="chip"><?= e($p['tier']) ?></span></h3>
      <p><?= nl2br(e($p['content'])) ?></p>
      <p class="muted">Par <?= e($p['author']) ?></p>
    </article>
  <?php endforeach; ?>
</section>

<?php if (!$isPremium): ?>
<div class="alert">Passe Premium pour voir tout le contenu motivation premium. <a href="pricing.php">Voir offres</a></div>
<?php endif; ?>
<?php require_once __DIR__ . '/_bottom.php'; ?>
