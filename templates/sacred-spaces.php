<?php
$theme=theme_resolve('sacred-spaces');
$types=[
  ['floor mandir','Floor Mandirs','Grand presence · integrated storage','⌂'],
  ['wall mandir','Wall-Mounted','Space-saving sacred furniture','◇'],
  ['tabletop mandir','Tabletop Temples','Compact daily prayer shrines','△'],
  ['led','Illuminated','Warm devotional ambience','✦'],
  ['storage','Storage Mandirs','Sacred space with quiet utility','▣'],
];
?>
<section class="sacred-hero sacred-luxury-hero">
  <div class="sacred-aura" aria-hidden="true"></div>
  <div class="shell sacred-hero-grid">
    <div class="sacred-copy reveal">
      <div class="sacred-eyebrow"><span></span><p class="kicker">Sacred Spaces · <?=e($theme['label'])?></p></div>
      <h1>Sacred spaces<br><em>for meaningful living.</em></h1>
      <p>Handcrafted mandirs, pooja consoles and devotional furniture shaped for modern homes—where warm light, thoughtful storage and material depth create a calmer everyday ritual.</p>
      <div class="hero-actions">
        <a class="btn sacred-primary" href="#mandir-collection">Explore the collection <span>→</span></a>
        <a class="btn sacred-ghost" href="<?= e(url('/bespoke/request?slug=shubh-labh-heritage-floor-mandir')) ?>">Commission a bespoke mandir</a>
      </div>
      <div class="sacred-traits" aria-label="Collection benefits">
        <span><b>01</b> Handcrafted in India</span><span><b>02</b> Made to order</span><span><b>03</b> Configurable finishes</span><span><b>04</b> Design Freeze workflow</span>
      </div>
      <div class="sacred-signature"><span class="sacred-signature-mark">✦</span><p><small>Collection direction</small><strong><?=e($theme['description'])?></strong></p></div>
    </div>
    <div class="sacred-hero-media reveal">
      <div class="sacred-arch-frame">
        <div class="sacred-arch-line" aria-hidden="true"></div>
        <img src="<?= e(image_url('shri-yantra-lotus-floor-mandir.webp')) ?>" alt="White handcrafted floor mandir with illuminated sacred back panel" fetchpriority="high">
        <div class="sacred-hero-caption"><span>Atelier Sacred No. 01</span><strong>Light · Material · Ritual</strong></div>
      </div>
    </div>
  </div>
</section>

<section class="sacred-type-band">
  <div class="shell sacred-types" aria-label="Mandir types">
    <?php foreach($types as [$key,$label,$desc,$symbol]): ?>
    <a class="sacred-type <?= $type===$key?'active':'' ?>" href="<?= e(url('/collections/sacred-spaces?type='.rawurlencode($key))) ?>"><span class="sacred-type-icon" aria-hidden="true"><?=e($symbol)?></span><span><strong><?= e($label) ?></strong><small><?= e($desc) ?></small></span></a>
    <?php endforeach; ?>
    <a class="sacred-type <?= $type===''?'active':'' ?>" href="<?= e(url('/collections/sacred-spaces')) ?>"><span class="sacred-type-icon" aria-hidden="true">∞</span><span><strong>All Mandirs</strong><small>Explore the complete collection</small></span></a>
  </div>
</section>

<section id="mandir-collection" class="section sacred-collection shell">
  <div class="section-head sacred-head">
    <div><p class="kicker">The collection</p><h2>Designed for devotion.<br><em>Resolved for today.</em></h2></div>
    <div class="sacred-head-note"><span><?= count($items) ?> pieces</span><p>Made-to-order furniture with configurable wood, finish and dimensions. Staging prices and engineering details require workshop sign-off.</p></div>
  </div>
  <?php if($items): ?><div class="product-grid sacred-product-grid"><?php foreach($items as $p) include __DIR__.'/_product-card.php'; ?></div><?php else: ?><div class="empty-state"><h2>No pieces in this view.</h2><a class="btn dark" href="<?= e(url('/collections/sacred-spaces')) ?>">View all mandirs</a></div><?php endif; ?>
</section>

<section class="sacred-editorial">
  <div class="shell sacred-editorial-grid">
    <div class="sacred-editorial-copy reveal">
      <p class="kicker">A quieter kind of luxury</p>
      <h2>Not ornament for ornament’s sake.</h2>
      <p>Proportion, illumination, storage and material are treated as one composition. The result is a sacred piece that feels intentional within the architecture of your home—not added after it.</p>
      <dl class="sacred-specs"><div><dt>Material direction</dt><dd>Walnut · teak · warm ivory</dd></div><div><dt>Lighting</dt><dd>Soft integrated ambience</dd></div><div><dt>Customisation</dt><dd>Dimensions · finish · storage</dd></div></dl>
    </div>
    <div class="sacred-editorial-quote reveal"><span>“</span><blockquote>A sacred space should feel luminous before a lamp is even lit.</blockquote><small>Atelier Wood · Sacred Spaces</small></div>
  </div>
</section>

<section class="section sacred-story">
 <div class="shell sacred-story-grid">
   <div class="sacred-story-media reveal"><div class="sacred-story-frame"><img src="<?= e(image_url('vrindavan-jali-floor-mandir.webp')) ?>" alt="Carved walnut floor mandir with jali doors" loading="lazy"></div></div>
   <div class="reveal sacred-story-copy"><p class="kicker">Crafted for devotion</p><h2>Tradition,<br><em>with contemporary restraint.</em></h2><p>Begin with a curated design, configure material and finish, or move into the Bespoke Studio for a controlled revision, customer approval, deposit and Design Freeze before workshop production.</p><div class="sacred-story-points"><span>Configurator-ready</span><span>Bespoke revision control</span><span>Workshop traceability</span></div><div class="inline-actions"><a class="btn sacred-light" href="<?= e(url('/shop?category=sacred-spaces')) ?>">Shop Sacred Spaces</a><a class="btn sacred-outline-light" href="<?= e(url('/about')) ?>">Discover our craft</a></div></div>
 </div>
</section>

<section class="section sacred-consult shell">
  <div class="sacred-consult-inner">
    <div><p class="kicker">Need a specific size?</p><h2>Design a mandir around your room.</h2><p>Use Room Planner to understand placement or start a bespoke project for custom dimensions, storage, lighting and finish.</p></div>
    <div class="inline-actions"><a class="btn sacred-primary" href="<?=e(url(customer_logged_in()?'/account/rooms':'/account/login'))?>">Open Room Planner</a><a class="btn sacred-ghost" href="<?=e(url('/bespoke/request?slug=shubh-labh-heritage-floor-mandir'))?>">Start bespoke project</a></div>
  </div>
</section>
