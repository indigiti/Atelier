<section class="page-hero shell compact wishlist-heading">
  <div><p class="kicker">Saved pieces</p><h1>My Wishlist</h1></div>
  <p data-wishlist-count><?=count($items)?> <?=count($items)===1?'item':'items'?></p>
</section>
<section class="section section-tight shell" data-wishlist-page>
  <div class="product-grid wishlist-grid" data-wishlist-grid <?= !$items?'hidden':'' ?>>
    <?php foreach($items as $p) include __DIR__.'/_product-card.php'; ?>
  </div>
  <div class="empty-state" data-wishlist-empty <?= $items?'hidden':'' ?>>
    <h2>Nothing saved yet.</h2><p>Tap the heart on a product to keep it here.</p>
    <a class="btn dark" href="<?=e(url('/shop'))?>">Browse products</a>
  </div>
</section>
