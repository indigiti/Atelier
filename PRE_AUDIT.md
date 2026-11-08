# Pre-Build Audit — v0.9.8.1 Commerce UX / Marketplace Luxe

## Baseline

The build started from the previously verified v0.9.8 staging release. The pre-build audit preserved the frozen security, identity, commerce, configuration, manufacturing and outward-serialization contracts.

## Responsive problems found before implementation

- **1024px was the weakest breakpoint:** the long desktop navigation remained while the large search experience disappeared.
- **820px PDP was conversion-poor:** the first viewport was dominated by imagery and delayed the product name, price and purchase state.
- tablet and mobile discovery lacked mature marketplace patterns for category shortcuts, filter/sort and search-led browsing.
- the mobile category and room discovery rails needed stronger containment and dedicated scroll behaviour.
- product cards were visually premium but lacked enough commercial information for fast comparison.
- Sacred Luxury was strong visually and was explicitly marked **preserve**, not redesign.

## Build rules frozen before coding

- 1280+ desktop: full search-first header + mega menu + 4-column PLP where space permits.
- 1024–1279 compact desktop/tablet landscape: compact header + dedicated search row + 3-column PLP.
- 768–1023 tablet portrait: hamburger/header + dedicated search + 2-column PLP; no phone-style bottom nav.
- 360–767 mobile: compact header, search row, 2-column PLP, filter/sort sheets and fixed five-item bottom navigation.
- PDP on tablet/mobile must expose title + price before or with the main gallery; purchase actions remain immediately discoverable.
- ThemeOS / Sacred Luxury / Configurator / My Spaces / TradeOS must remain compatible.
