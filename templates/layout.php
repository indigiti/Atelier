<?php
$app = $GLOBALS['app'];
$pageTitle = isset($title) ? $title . ' — ' . $app['name'] : $app['name'];
$pageDescription = $description ?? 'Handcrafted wooden objects for beautiful living.';
$pageRobots = $robots ?? 'index,follow,max-image-preview:large';
$pageCanonical = $canonical ?? absolute_url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$pageOgType = $og_type ?? 'website';
$themeSurface = $themeSurface ?? 'default';
$pageTheme = function_exists('theme_resolve') ? theme_resolve($themeSurface) : ['key'=>'atelier-natural','surface'=>$themeSurface,'tokens'=>['bg'=>'#f4efe6']];
$themeStyle = function_exists('theme_css_variables') ? theme_css_variables($pageTheme) : '';
$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="<?= e($pageTheme['tokens']['bg'] ?? '#f4efe6') ?>">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="robots" content="<?= e($pageRobots) ?>">
<link rel="canonical" href="<?= e($pageCanonical) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:type" content="<?= e($pageOgType) ?>">
<meta property="og:url" content="<?= e($pageCanonical) ?>">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<?php if (!empty($schema)): ?><script type="application/ld+json" nonce="<?= e($GLOBALS['csp_nonce'] ?? '') ?>"><?= json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?></script><?php endif; ?>
</head>
<body class="page-theme page-theme-<?= e($pageTheme['key'] ?? 'atelier-natural') ?> surface-<?= e(str_replace('_','-',$pageTheme['surface'] ?? 'default')) ?>" style="<?= e($themeStyle) ?>" data-page-theme="<?= e($pageTheme['key'] ?? 'atelier-natural') ?>">
<a class="skip-link" href="#main">Skip to content</a>
<div class="utility-bar"><div class="shell utility-inner"><span>STAGING · Handcrafted in India · <?= e($app['version']) ?></span><nav aria-label="Utility navigation"><a href="<?= e(url('/style-finder#consult')) ?>">Design consultation</a><a href="<?= e(url(customer_logged_in()?'/account/orders':'/account/login')) ?>">Order status</a><a href="<?= e(url('/trade')) ?>">Trade</a></nav></div></div>
<header class="site-header marketplace-header" data-header>
  <div class="shell nav-row commerce-nav-row">
    <button class="icon-button compact-only" type="button" data-menu-toggle aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button>
    <a class="brand" href="<?= e(url('/')) ?>" aria-label="Atelier Wood home"><span class="brand-main">Atelier Wood</span><span class="brand-sub"><?= e($app['tagline']) ?></span></a>
    <form class="header-search header-search-primary" action="<?= e(url('/shop')) ?>" method="get" role="search"><input type="search" name="q" placeholder="Search furniture, spaces, materials…" aria-label="Search products"><button type="submit" aria-label="Search">⌕</button></form>
    <div class="nav-actions">
      <button class="icon-button theme-desktop" type="button" data-theme-toggle aria-label="Toggle light and dark theme">◐</button>
      <a class="icon-link wishlist-desktop" href="<?= e(url('/wishlist')) ?>" aria-label="Wishlist">♡</a>
      <a class="icon-link account-link" href="<?= e(url(customer_logged_in()?'/account':'/account/login')) ?>" aria-label="Account"><?= customer_logged_in() ? 'Account' : 'Sign in' ?></a>
      <a class="icon-link bag" href="<?= e(url('/cart')) ?>" aria-label="Cart">Bag<?php if(cart_count()>0): ?><span><?= cart_count() ?></span><?php endif; ?></a>
    </div>
  </div>
  <div class="tablet-search-row"><div class="shell"><form class="header-search tablet-search" action="<?= e(url('/shop')) ?>" method="get" role="search"><input type="search" name="q" placeholder="Search products, rooms, materials…" aria-label="Search products"><button type="submit">Search</button></form></div></div>
  <div class="category-nav-wrap">
    <div class="shell category-nav-shell">
      <nav class="category-nav" aria-label="Shop navigation">
        <button class="mega-trigger" type="button" data-mega-toggle aria-expanded="false" aria-controls="shop-mega">Shop <span>⌄</span></button>
        <a href="<?= e(url('/collections/sacred-spaces')) ?>">Sacred Spaces</a><a href="<?= e(url('/shop?category=wall-shelves')) ?>">Wall & Shelves</a><a href="<?= e(url('/shop?category=ritual-incense')) ?>">Ritual</a><a href="<?= e(url('/shop?category=plant-displays')) ?>">Plant Displays</a><a href="<?= e(url('/shop?category=utility-gifting')) ?>">Gifts</a><a href="<?= e(url('/shop?category=outdoor-accents')) ?>">Outdoor</a><a href="<?= e(url('/materials')) ?>">Materials</a><a href="<?= e(url(customer_logged_in()?'/account/spaces':'/account/login')) ?>">Rooms</a><a href="<?= e(url('/account/quotes')) ?>">Custom Made</a>
      </nav>
    </div>
    <div class="mega-menu" id="shop-mega" data-mega hidden>
      <div class="shell mega-grid">
        <section><p class="mega-kicker">Shop by category</p><a href="<?= e(url('/shop?category=wall-shelves')) ?>"><strong>Wall & Shelves</strong><small>Floating, modular and statement storage</small></a><a href="<?= e(url('/shop?category=ritual-incense')) ?>"><strong>Ritual & Incense</strong><small>Quiet objects for everyday rituals</small></a><a href="<?= e(url('/shop?category=plant-displays')) ?>"><strong>Plant Displays</strong><small>Propagation and greenery pieces</small></a></section>
        <section><p class="mega-kicker">Shop by room</p><a href="<?= e(url('/shop?room=living')) ?>"><strong>Living Room</strong><small>Display, utility and wall pieces</small></a><a href="<?= e(url('/shop?room=sacred')) ?>"><strong>Sacred Space</strong><small>Floor, wall and tabletop mandirs</small></a><a href="<?= e(url('/shop?room=greenery')) ?>"><strong>Balcony & Greenery</strong><small>Plant displays and outdoor accents</small></a></section>
        <section><p class="mega-kicker">Design services</p><a href="<?= e(url('/style-finder')) ?>"><strong>Style Finder</strong><small>Find a material and design direction</small></a><a href="<?= e(url(customer_logged_in()?'/account/rooms':'/account/login')) ?>"><strong>Room Planner</strong><small>Plan with real dimensions</small></a><a href="<?= e(url('/account/quotes')) ?>"><strong>Bespoke Studio</strong><small>Revision-controlled custom projects</small></a></section>
        <a class="mega-feature" href="<?= e(url('/collections/sacred-spaces')) ?>"><img src="<?= e(image_url('shri-yantra-lotus-floor-mandir.webp')) ?>" alt="Sacred Spaces handcrafted mandir"><span>Collection story</span><strong>Sacred Luxury</strong><small>Handcrafted mandirs for modern homes →</small></a>
      </div>
    </div>
  </div>
  <div class="mobile-menu" data-menu hidden>
    <div class="mobile-menu-head"><span>Browse Atelier Wood</span><button type="button" class="menu-theme" data-theme-toggle>Theme ◐</button></div>
    <a href="<?= e(url('/shop')) ?>">Shop all</a><a href="<?= e(url('/shop?room=living')) ?>">Living Room</a><a href="<?= e(url('/collections/sacred-spaces')) ?>">Sacred Spaces / Mandirs</a><a href="<?= e(url('/shop?category=wall-shelves')) ?>">Wall & Shelves</a><a href="<?= e(url('/shop?category=ritual-incense')) ?>">Ritual & Incense</a><a href="<?= e(url('/shop?category=plant-displays')) ?>">Plant Displays</a><a href="<?= e(url('/shop?category=utility-gifting')) ?>">Gifts</a><a href="<?= e(url('/materials')) ?>">Materials</a><a href="<?= e(url('/style-finder')) ?>">Style Finder</a><a href="<?= e(url(customer_logged_in()?'/account/rooms':'/account/login')) ?>">Room Planner</a><a href="<?= e(url('/account/quotes')) ?>">Custom Made</a><a href="<?= e(url('/trade')) ?>">Trade / Architects</a><a href="<?= e(url(customer_logged_in()?'/account':'/account/login')) ?>"><?= customer_logged_in() ? 'My account' : 'Sign in / Register' ?></a>
  </div>
</header>
<?php if ($success || $error): ?><div class="shell flash-wrap"><div class="flash <?= $error ? 'error':'success' ?>"><?= e($error ?: $success) ?></div></div><?php endif; ?>

<main id="main"><?= $content ?></main>

<footer class="site-footer">
  <div class="shell footer-grid">
    <div>
      <div class="footer-brand">Atelier Wood</div>
      <p>Natural materials, quiet utility and handcrafted objects designed to make everyday spaces feel considered.</p>
    </div>
    <div><h3>Shop</h3><a href="<?= e(url('/shop')) ?>">All products</a><a href="<?= e(url('/collections/sacred-spaces')) ?>">Sacred Spaces</a><a href="<?= e(url('/shop?category=wall-shelves')) ?>">Wall shelves</a><a href="<?= e(url('/shop?category=ritual-incense')) ?>">Ritual & incense</a><a href="<?= e(url('/shop?category=plant-displays')) ?>">Plant displays</a></div>
    <div><h3>Studio</h3><a href="<?= e(url('/style-finder')) ?>">Style Finder</a><a href="<?= e(url(customer_logged_in()?'/account/spaces':'/account/login')) ?>">My Spaces</a><a href="<?= e(url(customer_logged_in()?'/account/rooms':'/account/login')) ?>">Room Planner</a><a href="<?= e(url('/materials')) ?>">Material library</a><a href="<?= e(url('/journal')) ?>">Journal</a><a href="<?= e(url('/trade')) ?>">Trade / Architects</a><a href="<?= e(url('/about')) ?>">Our craft</a></div>
    <div><h3>Staging notes</h3><p>Prices and dimensions are indicative staging content and require commercial sign-off before production.</p><a href="<?= e(url('/health')) ?>">System health</a></div>
  </div>
  <div class="shell footer-bottom"><span>© 2026 Atelier Wood</span><span>Staging build <?= e($app['version']) ?></span></div>
</footer>

<nav class="mobile-bottom commerce-bottom" aria-label="Mobile navigation">
  <a href="<?= e(url('/')) ?>"><span>⌂</span>Home</a>
  <a href="<?= e(url('/shop')) ?>"><span>▦</span>Shop</a>
  <a href="<?= e(url('/wishlist')) ?>"><span>♡</span>Saved</a>
  <a href="<?= e(url('/cart')) ?>"><span>▣</span>Cart<?php if(cart_count()>0): ?><b><?= cart_count() ?></b><?php endif; ?></a>
  <a href="<?= e(url(customer_logged_in()?'/account':'/account/login')) ?>"><span>◎</span>Account</a>
</nav>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
