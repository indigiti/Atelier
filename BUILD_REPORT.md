# Build Report — Atelier Wood / LivingOS v0.9.8.1

## Release

**Commerce UX / Marketplace Luxe — Verified Staging Release**

## What changed

- Search-first marketplace header with desktop mega navigation and compact tablet/mobile navigation.
- Tablet-specific search row so 1024–820px users no longer lose search when desktop navigation collapses.
- Visual category rail, Shop by Room discovery, marketplace search, room chips, advanced material/finish/price/customisable filters and deterministic sorting.
- Product listing cards tuned for desktop/tablet/mobile density without turning the storefront into a discount-heavy marketplace.
- Product-detail hierarchy rebuilt so product title, price and lead time appear in the first viewport on tablet/mobile.
- `Does it fit my room?` bridge to DesignOS / My Spaces.
- Pincode/serviceability block and sticky mobile purchase bar.
- Sacred Luxury preserved as a collection-specific ThemeOS identity.
- Responsive overflow and touch-target hardening after the final end-user visual audit.

## Functional verification

The cumulative regression suite passes:

Foundation → PIM → Commerce → Configurator → Bespoke → MakeOS → Procurement → Quality → Fulfilment → CMS/SEO → DesignOS → Sacred Spaces → ThemeOS → MediaOS → Visual Intelligence → Product Passport → TradeOS → Merchant/AI/Agent integrations → Security → Commerce UX → Release Regression.

`FINAL_REGRESSION_LOG.txt` ends with:

`PASS: full v0.9.8.1 regression suite`

## Responsive visual verification

The final Marketplace Luxe build was rendered across **32 critical page/viewport combinations**:

- Desktop: 1440, 1280
- Tablet: 1024, 820, 768
- Mobile: 430, 390, 360
- Pages: Home, Shop/PLP, Product/PDP, Sacred Spaces

Final result:

- horizontal viewport overflow: **0**
- broken product/media images: **0**
- JavaScript/page errors: **0**
- first-viewport PDP summary failures: **0**
- representative interaction failures: **0**
- mobile touch-target warnings in audited primary controls: **0**

The audit found and fixed a real mobile horizontal bleed caused by room/category rails and increased the main mobile card/wishlist/footer hit areas.

## Media verification

- canonical products: **34**
- referenced product media checked: **38**
- decoded successfully: **38**
- missing media: **0**
- decode failures: **0**

See `MEDIA_VISUAL_INTEGRITY.txt`.

## HTTP/readiness verification

Focused live HTTP smoke testing passed for the homepage, marketplace filters/search, PDP, Sacred Spaces, Materials, Style Finder, TradeOS, public catalogue APIs, Merchant/AI/Agent feeds, `/health` and `/ready`.

`/ready` returned **HTTP 200 with 78 checks and 0 failures** in the final staging runtime.

## Status

**Verified for staging. Not production-certified.**

Production authority still requires v0.9.9 PostgreSQL shadow migration/cutover rehearsal, live payment/provider validation, rights-cleared production media, commercial/tax sign-off and physical-device browser verification.
