<?php
$mediaGallery = function_exists('mediaos_gallery') ? mediaos_gallery($product) : [];
$schema = configurator_schema_for_product($product);
$initial = $loadedConfig['selection'] ?? configurator_default_selection($product, $schema);
$initialQuote = $schema ? configurator_quote($product, $initial) : null;
$displayPrice = $initialQuote['unit_price'] ?? $product['price'];
$isCustom = !empty($product['customisation']['enabled']);
$madeToOrder = !empty($product['inventory']['made_to_order']);
$roomFitUrl = customer_logged_in()
    ? url('/account/rooms?fit=' . $product['slug'])
    : url('/account/login?next=' . rawurlencode('/product/' . $product['slug']));
?>
<section class="product-mobile-summary shell reveal">
  <p class="kicker"><?php echo e($product['category_label']); ?> · <?php echo e($product['sku']); ?></p>
  <h1><?php echo e($product['name']); ?></h1>
  <div class="mobile-price-row">
    <strong><?php echo money($displayPrice); ?></strong>
    <span><?php echo e($product['lead_time']); ?></span>
  </div>
</section>

<section class="product-page shell marketplace-pdp">
  <div class="product-gallery reveal" id="gallery">
    <?php foreach ($product['gallery'] as $i => $img): ?>
      <figure class="gallery-item <?php echo $i === 0 ? 'main' : ''; ?>">
        <img src="<?php echo e(image_url($img)); ?>"
             alt="<?php echo e($product['name'] . ($i ? ' detail' : '')); ?>"
             <?php echo $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
      </figure>
    <?php endforeach; ?>
  </div>

  <div class="product-info reveal">
    <div class="desktop-product-summary">
      <p class="kicker"><?php echo e($product['category_label']); ?> · <?php echo e($product['sku']); ?></p>
      <h1><?php echo e($product['name']); ?></h1>
      <p class="product-short"><?php echo e($product['short']); ?></p>
      <div class="product-price" data-config-price><?php echo money($displayPrice); ?> <small>configured staging price</small></div>
    </div>

    <div class="purchase-signals">
      <span><?php echo $madeToOrder ? 'Made to order' : 'Available'; ?></span>
      <span><?php echo e($product['lead_time']); ?></span>
      <?php if ($isCustom): ?><span>Custom sizing</span><?php endif; ?>
    </div>

    <div class="info-grid">
      <div><span>Material</span><b><?php echo e($product['material']); ?></b></div>
      <div><span>Finish</span><b><?php echo e($product['finish']); ?></b></div>
      <div><span>Base dimensions</span><b><?php echo e($product['dimensions']); ?></b></div>
      <div><span>Lead time</span><b><?php echo e($product['lead_time']); ?></b></div>
    </div>

    <div class="pdp-service-grid">
      <a class="fit-room-card" href="<?php echo e($roomFitUrl); ?>">
        <span>⌗</span><div><small>Does it fit my room?</small><strong>Check in Room Planner</strong></div><b>→</b>
      </a>
      <div class="pincode-card" data-pincode-widget data-shipping-url="<?php echo e(url('/api/shipping')); ?>">
        <label for="pdp-pin">Delivery check</label>
        <div><input id="pdp-pin" inputmode="numeric" maxlength="6" placeholder="6-digit pincode"><button type="button" data-pincode-check>Check</button></div>
        <small data-pincode-result>See serviceability, ETA and installation availability.</small>
      </div>
    </div>

    <?php if ($schema): ?>
      <div class="configurator-shell" id="configure" data-configurator data-quote-url="<?php echo e(url('/api/configurator/quote')); ?>">
        <div class="configurator-head">
          <div><p class="kicker">Atelier configurator</p><h2>Make it yours.</h2></div>
          <span class="live-price-badge">Live staging price</span>
        </div>

        <?php if ($loadedConfig): ?>
          <div class="config-loaded-note">Loaded saved configuration: <strong><?php echo e($loadedConfig['label']); ?></strong></div>
        <?php endif; ?>

        <form class="config-form" method="post" action="<?php echo e(url('/cart/add')); ?>" data-config-form>
          <?php echo csrf_field(); ?>
          <input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>">
          <input type="hidden" name="configuration_guid" value="<?php echo e($loadedConfig['guid'] ?? ''); ?>">
          <div class="config-options">
            <?php foreach ($schema['options'] as $opt): ?>
              <?php $key = $opt['key']; $value = $initial[$key] ?? null; ?>
              <?php if ($opt['type'] === 'choice'): ?>
                <fieldset class="config-field">
                  <legend><?php echo e($opt['label']); ?></legend>
                  <div class="config-choice-grid">
                    <?php foreach ($opt['choices'] as $choice): ?>
                      <label class="config-choice">
                        <input type="radio" name="<?php echo e($key); ?>" value="<?php echo e($choice['value']); ?>" <?php echo ((string)$value === (string)$choice['value']) ? 'checked' : ''; ?>>
                        <span>
                          <b><?php echo e($choice['label']); ?></b>
                          <small><?php echo ((int)($choice['delta'] ?? 0) === 0) ? 'Included' : (((int)$choice['delta'] > 0 ? '+' : '') . money((int)$choice['delta'])); ?></small>
                        </span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </fieldset>
              <?php elseif ($opt['type'] === 'number'): ?>
                <label class="config-field range-field">
                  <span><?php echo e($opt['label']); ?></span>
                  <div><input type="range" name="<?php echo e($key); ?>" min="<?php echo e((string)$opt['min']); ?>" max="<?php echo e((string)$opt['max']); ?>" step="<?php echo e((string)$opt['step']); ?>" value="<?php echo e((string)$value); ?>" data-range><output><?php echo e((string)$value); ?> <?php echo e($opt['unit']); ?></output></div>
                  <small>Base <?php echo e((string)$opt['base']); ?> <?php echo e($opt['unit']); ?> · +<?php echo money((int)$opt['delta_per_step']); ?> per <?php echo e((string)$opt['step']); ?> <?php echo e($opt['unit']); ?> above base</small>
                </label>
              <?php elseif ($opt['type'] === 'boolean'): ?>
                <label class="config-toggle">
                  <input type="checkbox" name="<?php echo e($key); ?>" value="1" <?php echo !empty($value) ? 'checked' : ''; ?>>
                  <span><b><?php echo e($opt['label']); ?></b><small>+<?php echo money((int)$opt['delta']); ?></small></span>
                </label>
              <?php elseif ($opt['type'] === 'text'): ?>
                <label class="config-field">
                  <span><?php echo e($opt['label']); ?></span>
                  <input type="text" name="<?php echo e($key); ?>" value="<?php echo e((string)$value); ?>" maxlength="<?php echo e((string)$opt['max_length']); ?>" placeholder="<?php echo e($opt['placeholder'] ?? ''); ?>">
                  <small>Optional · +<?php echo money((int)$opt['delta']); ?></small>
                </label>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <div class="config-quote">
            <div><span>Base piece</span><b data-config-base><?php echo money($initialQuote['base_price'] ?? $product['price']); ?></b></div>
            <div data-config-adjustments>
              <?php foreach ($initialQuote['adjustments'] ?? [] as $a): ?>
                <span><?php echo e($a['label']); ?> <b>+<?php echo money($a['delta']); ?></b></span>
              <?php endforeach; ?>
            </div>
            <div class="config-total"><span>Your configured piece</span><strong data-config-total><?php echo money($displayPrice); ?></strong></div>
            <small data-config-status>Server-authoritative staging pricing.</small>
          </div>

          <label>Quantity<select name="qty"><?php for ($i=1; $i<=5; $i++): ?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php endfor; ?></select></label>
          <button class="btn primary wide" type="submit" data-add-config>Add to bag · <span><?php echo money($displayPrice); ?></span></button>
        </form>

        <?php if (customer_logged_in()): ?>
          <form class="save-config-form" method="post" action="<?php echo e(url('/configurations/save')); ?>" data-save-config>
            <?php echo csrf_field(); ?>
            <input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>">
            <input type="hidden" name="configuration_guid" value="<?php echo e($loadedConfig['guid'] ?? ''); ?>">
            <label>Save this design as<input name="label" maxlength="80" placeholder="e.g. Living room walnut shelf" value="<?php echo e($loadedConfig['label'] ?? ''); ?>"></label>
            <div data-save-config-fields></div>
            <button class="btn outline wide" type="submit"><?php echo $loadedConfig ? 'Update saved configuration' : 'Save configuration'; ?></button>
          </form>
        <?php else: ?>
          <a class="btn outline wide" href="<?php echo e(url('/account/login')); ?>">Sign in to save this configuration</a>
        <?php endif; ?>

        <div class="bespoke-config-cta">
          <div><p class="kicker">Beyond the configurator?</p><strong>Ask the studio to adapt this piece.</strong><small>Complex joinery, special dimensions, drawers, site-specific details and other workshop changes move into a revision-controlled bespoke quote.</small></div>
          <a class="btn outline" href="<?php echo e(url('/bespoke/request?slug=' . $product['slug'] . (!empty($loadedConfig['guid']) ? '&config=' . $loadedConfig['guid'] : ''))); ?>">Request bespoke adaptation</a>
        </div>
        <p class="config-disclaimer"><?php echo e($schema['notes'] ?? ''); ?></p>
      </div>
    <?php else: ?>
      <form class="config-form" id="configure" method="post" action="<?php echo e(url('/cart/add')); ?>">
        <?php echo csrf_field(); ?><input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>">
        <button class="btn primary wide" type="submit">Add to bag · <?php echo money($product['price']); ?></button>
      </form>
    <?php endif; ?>

    <form class="wishlist-inline" method="post" action="<?php echo e(url('/wishlist/toggle')); ?>">
      <?php echo csrf_field(); ?><input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>"><input type="hidden" name="back" value="<?php echo e('/product/' . $product['slug']); ?>">
      <button type="submit"><?php echo is_wishlisted($product['slug']) ? '♥ Saved' : '♡ Save to wishlist'; ?></button>
    </form>
    <div class="product-note">Staging note: live configurator pricing is deterministic and server-validated, but workshop dimensions, structural feasibility and commercial pricing require production sign-off.</div>
  </div>
</section>

<section class="section shell product-story-grid">
  <div><p class="kicker">Design intent</p><h2>Simple form.<br><em>Useful presence.</em></h2></div>
  <div><p><?php echo e($product['description']); ?></p><dl><dt>Care</dt><dd><?php echo e($product['care']); ?></dd><dt>Warranty</dt><dd><?php echo e($product['warranty']); ?></dd></dl></div>
</section>

<?php if ($related): ?>
<section class="section section-soft"><div class="shell">
  <div class="section-head"><div><p class="kicker">Complete the space</p><h2>Continue the composition.</h2></div><a class="text-link" href="<?php echo e(url('/shop?category=' . $product['category'])); ?>">See the collection →</a></div>
  <div class="product-grid product-grid-3"><?php foreach ($related as $p) include __DIR__ . '/_product-card.php'; ?></div>
</div></section>
<?php endif; ?>

<div class="mobile-purchase-bar">
  <div><small>From</small><strong data-mobile-pdp-price><?php echo money($displayPrice); ?></strong></div>
  <a class="btn outline" href="#configure">Customise</a><a class="btn primary" href="#configure">Add</a>
</div>
