# End-User Audit — v0.9.8.1 Marketplace Luxe

The milestone inherits the previously verified **59/59** end-to-end HTTP customer journey from v0.9.8 (registration → addresses → Style Finder → Room Planner → My Spaces → private visual analysis → wishlist/cart → checkout/sandbox payment → order history → TradeOS → Agent APIs/feeds → readiness).

For v0.9.8.1, the changed commerce surfaces were re-audited through live HTTP and responsive Chromium rendering.

## v0.9.8.1 focused live checks

PASS:

- Home
- Shop / marketplace PLP
- text search (`q=walnut`)
- room filter (`room=sacred`)
- combined price/customisable filter
- product detail page
- Sacred Spaces collection
- Materials
- Style Finder
- TradeOS
- health/readiness
- public catalogue APIs
- Merchant feed
- AI feed
- Agent product feed

HTML-level commerce markers for search, category rail, filter/sort controls, room-fit bridge, pincode widget, sticky mobile purchase bar and configurator target were also present in the live responses.

See `HTTP_SMOKE_AUDIT_FINAL.txt` and `END_USER_HTTP_AUDIT.txt`.
