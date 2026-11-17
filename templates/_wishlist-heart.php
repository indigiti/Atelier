<?php $heartSaved = is_wishlisted($wishlistProduct['slug']); ?>
<form class="wishlist-photo-form" method="post" action="<?=e(url('/wishlist/toggle'))?>" data-wishlist-form>
  <?=csrf_field()?>
  <input type="hidden" name="slug" value="<?=e($wishlistProduct['slug'])?>">
  <input type="hidden" name="back" value="<?=e($_SERVER['REQUEST_URI'] ?? url('/shop'))?>">
  <button class="heart-button wishlist-photo-heart<?=$heartSaved?' active':''?>" type="submit" aria-label="<?=e('Save '.$wishlistProduct['name'].' to wishlist')?>" aria-pressed="<?=$heartSaved?'true':'false'?>" data-wishlist-heart>
    <svg viewBox="0 0 24 24" width="21" height="21" aria-hidden="true"><path d="M12 20.3 4.6 13C.1 8.5 6.4 2.2 12 7.8 17.6 2.2 23.9 8.5 19.4 13Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
  </button>
</form>
