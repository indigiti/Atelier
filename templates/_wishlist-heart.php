<?php $heartSaved = is_wishlisted($wishlistProduct['slug']); ?>
<form class="wishlist-photo-form" method="post" action="<?=e(url('/wishlist/toggle'))?>" data-wishlist-form>
  <?=csrf_field()?>
  <input type="hidden" name="slug" value="<?=e($wishlistProduct['slug'])?>">
  <input type="hidden" name="back" value="<?=e($_SERVER['REQUEST_URI'] ?? url('/shop'))?>">
  <button class="heart-button wishlist-photo-heart<?=$heartSaved?' active':''?>" type="submit" aria-label="<?=e('Save '.$wishlistProduct['name'].' to wishlist')?>" aria-pressed="<?=$heartSaved?'true':'false'?>" data-wishlist-heart>
    <?=lucide_icon('heart')?>
  </button>
</form>
