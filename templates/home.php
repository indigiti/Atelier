<section class="hero hero-home">
  <div class="hero-media"><img src="<?= e(image_url('incense-smoke.webp')) ?>" alt="Wooden incense holder with gentle smoke" fetchpriority="high"><div class="hero-overlay"></div></div>
  <div class="shell hero-inner">
    <div class="hero-copy reveal">
      <p class="kicker">Natural materials · meaningful spaces</p>
      <h1>Crafted objects<br><em>for beautiful living.</em></h1>
      <p class="hero-lead">Handcrafted wooden decor, shelves, ritual pieces, plant displays and thoughtful gifts—made for calmer, more personal homes.</p>
      <div class="hero-actions"><a class="btn primary" href="<?= e(url('/shop')) ?>">Explore the collection</a><a class="btn glass" href="<?= e(url('/style-finder')) ?>">Find my style</a></div>
    </div>
    <div class="hero-note"><span>Small objects.</span><strong>Bigger moments.</strong></div>
  </div>
</section>

<section class="trust-strip"><div class="shell trust-grid"><div><span>⌁</span><b>Handcrafted with care</b><small>Small-batch making</small></div><div><span>♧</span><b>Material conscious</b><small>Wood-first construction</small></div><div><span>◇</span><b>Designed to last</b><small>Repairable, simple forms</small></div><div><span>⌂</span><b>Custom sizing</b><small>Selected pieces only</small></div></div></section>

<section class="section shell home-commerce-discovery">
  <div class="section-head commerce-section-head"><div><p class="kicker">Shop by room</p><h2>Start with<br><em>the space.</em></h2></div><p>Browse the way you furnish: by room, ritual or everyday need. Then refine by material, finish and size.</p></div>
  <div class="room-discovery-grid">
    <a href="<?=e(url('/shop?room=living'))?>"><img src="<?=e(image_url('chevron-shelf.webp'))?>" alt="Living room wall shelf"><span><b>Living Room</b><small>Display, storage & useful objects</small></span></a>
    <a href="<?=e(url('/shop?room=sacred'))?>"><img src="<?=e(image_url('shri-yantra-lotus-floor-mandir.webp'))?>" alt="Sacred home mandir"><span><b>Sacred Space</b><small>Mandirs & pooja furniture</small></span></a>
    <a href="<?=e(url('/shop?room=greenery'))?>"><img src="<?=e(image_url('duo-propagation.webp'))?>" alt="Plant display"><span><b>Greenery & Balcony</b><small>Plant displays & outdoor accents</small></span></a>
    <a href="<?=e(url('/shop?room=gifting'))?>"><img src="<?=e(image_url('bead-key-holder.webp'))?>" alt="Wooden gift object"><span><b>Gifting</b><small>Thoughtful, personal everyday pieces</small></span></a>
  </div>
</section>

<section class="home-category-strip section-soft"><div class="shell"><div class="quick-category-row"><a href="<?=e(url('/shop?category=wall-shelves'))?>"><span>01</span>Wall & Shelves</a><a href="<?=e(url('/shop?category=ritual-incense'))?>"><span>02</span>Ritual</a><a href="<?=e(url('/shop?category=plant-displays'))?>"><span>03</span>Plant Displays</a><a href="<?=e(url('/shop?category=utility-gifting'))?>"><span>04</span>Gifts & Utility</a><a href="<?=e(url('/collections/sacred-spaces'))?>"><span>05</span>Sacred Spaces</a><a href="<?=e(url('/account/quotes'))?>"><span>06</span>Custom Made</a></div></div></section>

<section class="section shell">
  <div class="section-head reveal"><div><p class="kicker">Shop by world</p><h2>Useful objects,<br><em>beautifully resolved.</em></h2></div><p>Start with the moment you want to improve: display, ritual, greenery, gifting or utility.</p></div>
  <div class="category-grid">
    <a class="category-card reveal" href="<?= e(url('/shop?category=wall-shelves')) ?>"><img src="<?= e(image_url('nordic-ladder.webp')) ?>" alt="Wood wall shelves"><div><span>01</span><h3>Wall Shelves</h3><p>Display with rhythm.</p></div></a>
    <a class="category-card reveal" href="<?= e(url('/shop?category=ritual-incense')) ?>"><img src="<?= e(image_url('zen-wave.webp')) ?>" alt="Wood incense holder"><div><span>02</span><h3>Ritual & Incense</h3><p>Objects for quiet rituals.</p></div></a>
    <a class="category-card reveal" href="<?= e(url('/shop?category=plant-displays')) ?>"><img src="<?= e(image_url('duo-propagation.webp')) ?>" alt="Wood propagation stand"><div><span>03</span><h3>Plant Displays</h3><p>Bring nature in.</p></div></a>
    <a class="category-card reveal" href="<?= e(url('/shop?category=utility-gifting')) ?>"><img src="<?= e(image_url('bead-key-holder.webp')) ?>" alt="Wooden key holder"><div><span>04</span><h3>Utility & Gifting</h3><p>Everyday, made thoughtful.</p></div></a>
    <a class="category-card reveal" href="<?= e(url('/shop?category=outdoor-accents')) ?>"><img src="<?= e(image_url('cedar-bird-feeder.webp')) ?>" alt="Geometric wooden bird feeder"><div><span>05</span><h3>Outdoor Accents</h3><p>Small architecture outdoors.</p></div></a>
    <a class="category-card reveal" href="<?= e(url('/collections/sacred-spaces')) ?>"><img src="<?= e(image_url('shri-yantra-lotus-floor-mandir.webp')) ?>" alt="Handcrafted white home mandir"><div><span>06</span><h3>Sacred Spaces</h3><p>Mandirs for modern rituals.</p></div></a>
  </div>
</section>

<section class="section section-soft">
  <div class="shell">
    <div class="section-head reveal"><div><p class="kicker">Featured collection</p><h2>Our handcrafted<br><em>favorites.</em></h2></div><a class="text-link" href="<?= e(url('/shop')) ?>">View all products →</a></div>
    <div class="product-grid"><?php foreach ($featured as $p) include __DIR__.'/_product-card.php'; ?></div>
  </div>
</section>

<section class="section shell split-feature">
  <div class="feature-image reveal"><img src="<?= e(image_url('hexanest-cubes.webp')) ?>" alt="Hexagonal wooden wall display cubes" loading="lazy"></div>
  <div class="feature-copy reveal"><p class="kicker">Build the wall</p><h2>Compose,<br><em>don’t clutter.</em></h2><p>Modular display pieces let you build a wall gradually. Start with one form, add a second finish, and let plants or objects create the living detail.</p><a class="btn dark" href="<?= e(url('/product/hexanest-wall-cubes')) ?>">Explore HexaNest</a></div>
</section>

<section class="section ritual-band">
  <div class="shell ritual-grid">
    <div class="ritual-copy reveal"><p class="kicker">Ritual objects</p><h2>A quieter way<br><em>to begin or end the day.</em></h2><p>Incense holders in ash, teak and walnut—kept simple enough to live beautifully even when not in use.</p><a class="btn light" href="<?= e(url('/shop?category=ritual-incense')) ?>">Shop ritual pieces</a></div>
    <div class="ritual-gallery"><img class="reveal" src="<?= e(image_url('kyoto-block.webp')) ?>" alt="Walnut incense block" loading="lazy"><img class="reveal" src="<?= e(image_url('om-ritual.webp')) ?>" alt="Hand-burnished ritual incense planks" loading="lazy"></div>
  </div>
</section>

<section class="section shell craft-story">
  <div class="craft-panel reveal"><p class="kicker">The Atelier way</p><h2>Rooted in material.<br><em>Crafted for life.</em></h2><p>We favour simple joinery, honest surfaces and pieces that can age with use. Staging products currently use curated reference imagery and indicative specifications while production materials and dimensions are being locked.</p><div class="inline-actions"><a class="btn dark" href="<?= e(url('/about')) ?>">Our craft</a><a class="btn outline" href="<?= e(url('/materials')) ?>">Material library</a></div></div>
  <div class="craft-stats reveal"><div><strong>34</strong><span>staging products</span></div><div><strong>6</strong><span>product worlds</span></div><div><strong>3–14d</strong><span>indicative lead times</span></div><div><strong>WebP</strong><span>optimized imagery</span></div></div>
</section>

<section class="section shell consult-card reveal"><div><p class="kicker">DesignOS · v0.8</p><h2>Find the direction.<br><em>Then plan the room.</em></h2></div><div><p>Use the Style Finder for a material and product direction, then build a measured private room plan with budget and overlap guidance.</p><div class="inline-actions"><a class="btn primary" href="<?= e(url('/style-finder')) ?>">Run Style Finder</a><a class="btn outline" href="<?= e(url(customer_logged_in()?'/account/rooms':'/account/login')) ?>">Room Planner</a></div></div></section>
