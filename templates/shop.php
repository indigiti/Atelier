<?php
$filters=$filters??[];$filterOptions=$filterOptions??['materials'=>[],'finishes'=>[],'rooms'=>[]];
$categoryLinks=[
 ['slug'=>'wall-shelves','label'=>'Wall & Shelves','img'=>'nordic-ladder.webp','copy'=>'Display, storage and sculptural wall pieces.'],
 ['slug'=>'sacred-spaces','label'=>'Sacred Spaces','img'=>'shri-yantra-lotus-floor-mandir.webp','copy'=>'Floor, wall and tabletop mandirs.'],
 ['slug'=>'ritual-incense','label'=>'Ritual & Incense','img'=>'zen-wave.webp','copy'=>'Quiet objects for daily rituals.'],
 ['slug'=>'plant-displays','label'=>'Plant Displays','img'=>'duo-propagation.webp','copy'=>'Wood-and-glass greenery pieces.'],
 ['slug'=>'utility-gifting','label'=>'Gifts & Utility','img'=>'bead-key-holder.webp','copy'=>'Thoughtful everyday objects.'],
 ['slug'=>'outdoor-accents','label'=>'Outdoor Accents','img'=>'cedar-bird-feeder.webp','copy'=>'Small architecture for open spaces.'],
];
function shop_filter_fields(array $filters,array $filterOptions): void { ?>
  <label>Material<select name="material"><option value="">All materials</option><?php foreach($filterOptions['materials'] as $v):?><option value="<?=e($v)?>" <?=($filters['material']??'')===$v?'selected':''?>><?=e($v)?></option><?php endforeach;?></select></label>
  <label>Finish<select name="finish"><option value="">All finishes</option><?php foreach($filterOptions['finishes'] as $v):?><option value="<?=e($v)?>" <?=($filters['finish']??'')===$v?'selected':''?>><?=e($v)?></option><?php endforeach;?></select></label>
  <label>Price<select name="price_max"><option value="0">Any price</option><?php foreach([2000,5000,10000,20000,30000] as $v):?><option value="<?=$v?>" <?=((int)($filters['price_max']??0)===$v)?'selected':''?>>Up to <?=money($v)?></option><?php endforeach;?></select></label>
  <label class="filter-check"><input type="checkbox" name="customisable" value="1" <?=!empty($filters['customisable'])?'checked':''?>><span>Customisable only</span></label>
<?php }
?>
<section class="page-hero marketplace-page-hero shell">
  <div><p class="kicker">The Atelier collection</p><h1>Find the piece<br><em>that fits your life.</em></h1><p>Browse by category, material, finish, room and customisation. Every product stays connected to Configurator, My Spaces and our bespoke studio.</p></div>
  <a class="consult-mini-card" href="<?=e(url('/style-finder'))?>"><span>Not sure where to start?</span><strong>Use Style Finder</strong><small>Get a room and material direction →</small></a>
</section>

<section class="shell commerce-category-rail" aria-label="Shop by category">
  <?php foreach($categoryLinks as $c):?><a class="commerce-category-card <?=($category??'')===$c['slug']?'active':''?>" href="<?=e(url('/shop?category='.$c['slug']))?>"><img src="<?=e(image_url($c['img']))?>" alt=""><span><strong><?=e($c['label'])?></strong><small><?=e($c['copy'])?></small></span></a><?php endforeach;?>
</section>

<section class="shell marketplace-search-band">
  <form class="marketplace-search" method="get" action="<?=e(url('/shop'))?>" role="search">
    <?php if(!empty($category)):?><input type="hidden" name="category" value="<?=e($category)?>"><?php endif;?>
    <label class="sr-only" for="shop-q">Search the collection</label><input id="shop-q" type="search" name="q" value="<?=e($q)?>" placeholder="Search products, materials, finishes…"><button>Search</button>
  </form>
  <div class="room-rail" aria-label="Shop by room"><?php foreach($filterOptions['rooms'] as $key=>$r):?><a class="filter <?=($filters['room']??'')===$key?'active':''?>" href="<?=e(url('/shop?room='.$key))?>"><?=e($r['label'])?></a><?php endforeach;?></div>
</section>

<section class="section section-tight shell shop-marketplace-layout">
  <aside class="desktop-filter-panel" aria-label="Product filters">
    <form method="get" action="<?=e(url('/shop'))?>" class="market-filter-form">
      <div class="filter-title"><strong>Filter</strong><a href="<?=e(url('/shop'))?>">Clear</a></div>
      <?php if($q!==''):?><input type="hidden" name="q" value="<?=e($q)?>"><?php endif;?><?php if($category!==''):?><input type="hidden" name="category" value="<?=e($category)?>"><?php endif;?><?php if(!empty($filters['room'])):?><input type="hidden" name="room" value="<?=e($filters['room'])?>"><?php endif;?>
      <?php shop_filter_fields($filters,$filterOptions); ?>
      <input type="hidden" name="sort" value="<?=e($filters['sort']??'recommended')?>"><button class="btn dark wide" type="submit">Apply filters</button>
    </form>
  </aside>
  <div class="market-results">
    <div class="market-results-head"><div><span><?=count($items)?> pieces</span><?php if($q):?><small>Search: “<?=e($q)?>”</small><?php endif;?></div><form method="get" action="<?=e(url('/shop'))?>" class="desktop-sort-form"><?php foreach($filters as $k=>$v):?><?php if($k!=='sort'&&$v!==''):?><input type="hidden" name="<?=e($k)?>" value="<?=e((string)$v)?>"><?php endif;?><?php endforeach;?><label>Sort <select name="sort" onchange="this.form.submit()"><option value="recommended" <?=($filters['sort']??'')==='recommended'?'selected':''?>>Recommended</option><option value="price_asc" <?=($filters['sort']??'')==='price_asc'?'selected':''?>>Price: Low to high</option><option value="price_desc" <?=($filters['sort']??'')==='price_desc'?'selected':''?>>Price: High to low</option><option value="name" <?=($filters['sort']??'')==='name'?'selected':''?>>Name</option></select></label></form></div>
    <div class="mobile-filterbar"><button type="button" data-filter-toggle>Filter<?php $activeCount=(int)!empty($filters['material'])+(int)!empty($filters['finish'])+(int)!empty($filters['price_max'])+(int)!empty($filters['customisable']);if($activeCount):?> <b><?=$activeCount?></b><?php endif;?></button><button type="button" data-sort-toggle>Sort</button></div>
    <?php if($items):?><div class="product-grid marketplace-product-grid"><?php foreach($items as $p)include __DIR__.'/_product-card.php';?></div><?php else:?><div class="empty-state"><h2>No pieces found.</h2><p>Try another material, finish or search term.</p><a class="btn dark" href="<?=e(url('/shop'))?>">View all products</a></div><?php endif;?>
  </div>
</section>

<div class="commerce-sheet" data-filter-sheet hidden><button class="sheet-backdrop" data-sheet-close aria-label="Close filters"></button><section class="sheet-panel" role="dialog" aria-modal="true" aria-label="Product filters"><header><strong>Filter products</strong><button type="button" data-sheet-close>×</button></header><form method="get" action="<?=e(url('/shop'))?>" class="market-filter-form"><?php if($q!==''):?><input type="hidden" name="q" value="<?=e($q)?>"><?php endif;?><?php if($category!==''):?><input type="hidden" name="category" value="<?=e($category)?>"><?php endif;?><?php shop_filter_fields($filters,$filterOptions);?><input type="hidden" name="sort" value="<?=e($filters['sort']??'recommended')?>"><button class="btn primary wide">Show <?=count($items)?> pieces</button></form></section></div>
<div class="commerce-sheet" data-sort-sheet hidden><button class="sheet-backdrop" data-sheet-close aria-label="Close sort"></button><section class="sheet-panel sort-panel" role="dialog" aria-modal="true" aria-label="Sort products"><header><strong>Sort products</strong><button type="button" data-sheet-close>×</button></header><nav><?php foreach(['recommended'=>'Recommended','price_asc'=>'Price: Low to high','price_desc'=>'Price: High to low','name'=>'Name'] as $key=>$label):$qv=$filters;$qv['sort']=$key;?><a class="<?=($filters['sort']??'recommended')===$key?'active':''?>" href="<?=e(url('/shop?'.http_build_query(array_filter($qv,fn($v)=>$v!==''&&$v!==0))))?>"><?=e($label)?></a><?php endforeach;?></nav></section></div>
