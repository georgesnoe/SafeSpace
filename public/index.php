<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';
?>
<section class="hero home-hero exact-hero" data-reveal>
  <div class="hero-svg-wrap" aria-label="SafeSpace hero scene">
    <?php
    $heroSvgPath = __DIR__ . '/../safespace_hero.svg';
    if (is_readable($heroSvgPath)) {
        echo file_get_contents($heroSvgPath);
    } else {
        echo '<p class="muted">Hero SVG introuvable.</p>';
    }
    ?>
  </div>
</section>

<section class="grid grid-3 overlap" data-reveal>
  <article class="card soft-card"><h3>Confiance radicale</h3><p class="muted">Infrastructure sobre, interfaces nettes, protection au premier plan.</p></article>
  <article class="card soft-card"><h3>Intimite moderne</h3><p class="muted">Espace discret pour s'exprimer librement sans exposition sociale.</p></article>
  <article class="card soft-card"><h3>Creator Economy</h3><p class="muted">De l'aide emotionnelle au contenu premium: un modele monetisable et durable.</p></article>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
