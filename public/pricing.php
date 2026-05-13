<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';
?>
<section class="hero page-hero">
  <h1>Monetisation Freemium</h1>
  <p class="muted">Paiement en FCFA pour debloquer du contenu exclusif.</p>
</section>
<section class="grid grid-3" style="margin-top:1rem;">
  <article class="card soft-card"><h3>Free - 0 FCFA</h3><p>Feed, partages anonymes, commentaires, inspirations standard.</p></article>
  <article class="card soft-card"><h3>Premium - 2 000 FCFA/mois</h3><p>Contenu motivation premium, journaux guides, bonus creator.</p>
    <?php if (!$user): ?>
      <p class="muted">Connecte-toi pour upgrader.</p>
    <?php else: ?>
      <?php if (isPremiumActive($user)): ?>
        <p><strong>Abonnement actif jusqu'au <?= e((string)($user['premium_expires_at'] ?? 'non defini')) ?>.</strong></p>
      <?php else: ?>
        <p><strong>Abonnement inactif.</strong></p>
      <?php endif; ?>
      <form method="post" action="subscribe.php">
        <button class="btn" type="submit"><?= isPremiumActive($user) ? 'Renouveler 2 000 FCFA / mois' : 'Payer 2 000 FCFA / mois' ?></button>
      </form>
    <?php endif; ?>
  </article>
  <article class="card soft-card"><h3>Creator - 12 900 FCFA/mois</h3><p>Publier des packs motivation premium et monétiser ta communauté.</p></article>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
