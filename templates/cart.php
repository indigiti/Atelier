<section class="page-hero shell compact"><p class="kicker">Your selection</p><h1>Shopping bag.</h1><p>Review your materials, finishes and quantities before secure checkout.</p></section>
<section class="section section-tight shell cart-layout">
  <div>
    <?php if(!$lines): ?><div class="empty-state"><h2>Your bag is empty.</h2><p>Start with a small object or build a whole wall composition.</p><a class="btn dark" href="<?= e(url('/shop')) ?>">Explore collection</a></div><?php else: ?>
    <form method="post" action="<?= e(url('/cart/update')) ?>"><?= csrf_field() ?>
      <div class="cart-lines">
      <?php foreach($lines as $line): $p=$line['p']; ?><article class="cart-line"><img src="<?= e(image_url($p['image'])) ?>" alt="<?= e($p['name']) ?>"><div class="cart-line-copy"><a href="<?= e(url('/product/'.$p['slug'])) ?>"><h3><?= e($p['name']) ?></h3></a><p><?= e($line['material']) ?> · <?= e($line['finish']) ?></p><?php if(!empty($line['configuration_summary'])):?><div class="cart-config-summary"><?php foreach(array_slice($line['configuration_summary'],2,5) as $cs): if(($cs['value']??'')==='Not selected'||($cs['value']??'')==='None')continue;?><span><?=e($cs['label'])?>: <b><?=e($cs['value'])?></b></span><?php endforeach;?></div><?php endif;?><?php if(!empty($line['pricing_changed'])):?><small class="price-change-note">Price refreshed to the current staging rule.</small><?php endif;?><strong><?= money($line['unit']) ?></strong></div><label>Qty<input type="number" min="0" max="10" name="qty[<?= e($line['key']) ?>]" value="<?= $line['qty'] ?>"></label><b><?= money($line['line_total']) ?></b></article><?php endforeach; ?>
      </div><button class="btn outline" type="submit">Update cart</button>
    </form>
    <?php endif; ?>
  </div>
  <aside class="order-summary"><p class="kicker">Summary</p><div><span>Items</span><b><?= cart_count() ?></b></div><div><span>Subtotal</span><b><?= money(cart_total()) ?></b></div><div><span>Shipping</span><b>Calculated by pincode</b></div><div class="summary-total"><span>Cart subtotal</span><strong><?= money(cart_total()) ?></strong></div><?php if($lines):?><a class="btn primary wide" href="<?=e(url(customer_logged_in()?'/checkout':'/account/login'))?>"><?=customer_logged_in()?'Proceed to checkout':'Sign in to checkout'?></a><?php endif;?><small>No real payment is collected in staging. Sandbox payment only.</small></aside>
</section>
