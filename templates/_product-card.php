<?php $isCustom=!empty($p['customisation']['enabled']);$madeToOrder=!empty($p['inventory']['made_to_order']);$material0=(string)($p['materials'][0]??$p['material']??'');$finish0=(string)($p['finishes'][0]??$p['finish']??''); ?>
<article class="product-card reveal marketplace-card">
  <div class="wishlist-photo-wrap">
  <a class="product-image" href="<?=e(url('/product/'.$p['slug']))?>">
    <img src="<?=e(image_url(mediaos_primary_file($p)))?>" alt="<?=e(mediaos_primary_alt($p))?>" loading="lazy" decoding="async" width="700" height="840">
    <span class="product-category"><?=e($p['category_label'])?></span>
    <span class="card-badges"><?php if($isCustom):?><b>Customisable</b><?php endif;?><?php if($madeToOrder):?><b>Made to order</b><?php endif;?></span>
  </a>
  <?php $wishlistProduct=$p; include __DIR__.'/_wishlist-heart.php'; ?>
  </div>
  <div class="product-card-body">
    <div class="product-title-row"><a href="<?=e(url('/product/'.$p['slug']))?>"><h3><?=e($p['name'])?></h3></a></div>
    <div class="product-meta-line"><?=e($material0)?><?php if($finish0):?> · <?=e($finish0)?><?php endif;?></div>
    <p class="product-description"><?=e($p['short'])?></p>
    <div class="product-commerce-line"><span><?=e($p['lead_time']??'Made to order')?></span><?php if($isCustom):?><span>Custom sizes available</span><?php endif;?></div>
    <div class="product-card-foot"><strong><small>From</small> <?=money($p['price'])?></strong><a href="<?=e(url('/product/'.$p['slug']))?>"><?= $isCustom?'Customise':'View' ?> →</a></div>
  </div>
</article>
