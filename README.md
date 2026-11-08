# Atelier Wood / LivingOS v0.9.8.1

## Commerce UX / Marketplace Luxe — Verified Staging Release

This release upgrades Atelier Wood from an editorial-first storefront into a mature search/filter/category commerce experience while preserving the Atelier visual identity, ThemeOS, Sacred Luxury, Configurator, DesignOS, My Spaces, Product Passport, TradeOS and MakeOS stack.

### Main additions

- search-first desktop header
- keyboard/escape-capable mega menu
- compact tablet header + dedicated search row
- visual category rail + Shop by Room
- marketplace search, material/finish/price/customisable filters and sort
- mobile/tablet filter and sort sheets
- denser but still premium product cards
- responsive PDP with first-viewport product identity/price
- `Does it fit my room?` DesignOS bridge
- pincode/serviceability panel
- sticky mobile purchase bar
- responsive commerce hardening at 1440/1280/1024/820/768/430/390/360 widths

## Verification summary

- cumulative regression: **PASS**
- PHP syntax: **119/119 files PASS**
- responsive visual audit: **32/32 page/viewport combinations PASS**
- representative UI interaction checks: **PASS**
- media integrity: **38/38 referenced assets decode, 0 missing**
- focused live HTTP marketplace/API/feed smoke test: **PASS**
- `/ready`: **HTTP 200, 78 checks, 0 failures**

## Staging setup

1. Point the web root to `public/`, not the project root.
2. Configure a unique 32-byte `ATELIER_DATA_KEY` outside source control.
3. Configure environment-specific webhook/payment/agent secrets.
4. Keep debug disabled on public staging.
5. Run `scripts/full_regression.sh`.
6. Open `/ready` and require HTTP 200 before exposing the environment.
7. Provision the first administrator through `/admin/setup` in a protected environment.

Example local staging server:

```bash
php -S 127.0.0.1:8080 -t public router.php
```

This remains **staging-only**. PostgreSQL authority is intentionally deferred to v0.9.9, payments remain sandbox until production gateway validation, and final media/specification/legal approval is still required.
