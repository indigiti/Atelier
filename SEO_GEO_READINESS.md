# Atelier SEO + AI Discoverability — Implementation & Release Gates

Updated: 2026-11-08

## Implemented in this milestone

- Canonical URLs are query-independent; filtered search and private/customer-specific routes are noindex.
- Staging pages send noindex through both HTTP X-Robots-Tag and HTML metadata. Production remains blocked by the file-authoritative bootstrap until the database migration is certified.
- Crawling is intentionally permitted on staging HTML so crawlers can *see* noindex; robots.txt disallows private actions. No production sitemap is advertised while staging.
- XML sitemap covers core published routes and public products, includes meaningful modification dates only when known, and excludes drafts and explicitly noindexed editorial records.
- Single-product JSON-LD uses Product + merchant Offer with INR, current base/default-config price, availability, verified catalogue SKU, seller reference and image URLs. No fabricated reviews, ratings, shipping guarantees, GTIN, MPN, return policies or certifications.
- Connected Organization, WebSite, WebPage, BreadcrumbList and Article JSON-LD, plus Open Graph image and Twitter metadata.
- Public JSON product feed, brand/product knowledge graph at /feeds/knowledge.json and optional /llms.txt navigation.
- SEO title and description fields for the version-controlled product catalogue were populated from existing product copy; the published test guide in version-controlled CMS content was returned to draft.
- /admin/seo shows per-product metadata, hero image/rights checks, XML validation, unverified external integration statuses, links to fix products and an authenticated diagnostics JSON export.
- CI covers metadata completeness, schema shape, sitemap/merchant XML, staging noindex, filtered-page rules, public data sanitization, catalogue coverage and the diagnostics.
- Inaccurate generic Google product category claim removed; allow Google's categorization or supply verified categories by product in a subsequent catalogue review.

## Important boundaries

**Technical SEO implemented does not mean indexing or ranking is achieved.** Google Search Console must independently verify that production pages are crawled and indexed. Google Merchant Center must accept the real product feed; feed XML validity alone does not establish Shopping eligibility. AI model citation or AI referral traffic cannot be promised.

**Production is not indexable in this release.** The PHP bootstrap intentionally rejects production mode. Do not bypass the production guard to expose staging/file-mode checkout. Only enable indexing on a separately certified production build after persistence, payment, policies, pricing, images, stock and fulfilment are approved.

**Images and claims:** MediaOS marks rights and status separately. Items tagged unverified or staging are flagged by the dashboard and cannot be treated as licensed or launch-ready. The catalogue's product prices, inventory and wording are staging data pending commercial verification.

**llms.txt:** It is an optional discovery document, not an industry-standard guarantee of access, citations, or ranking.

## Recommended go-live verification

1. Set the verified origin, HTTPS and allowed hosts; verify reverse-proxy canonical links, sitemap URLs, robots rules, indexability and web server response headers.
2. Approve real product names, specifications, availability, pricing, photography, copyright/right-to-use, return/shipping policies and actual customer checkout.
3. Confirm /admin/seo has no critical missing metadata or broken images; resolve rights warnings before indexing.
4. Run the full isolated CI suite and test Google Rich Results Test using live product URLs. Check actual Product and Offer eligibility, not just well-formed JSON-LD.
5. Register the production domain in Google Search Console; submit the live sitemap and use URL Inspection plus the Page Indexing report.
6. Link and verify the production Merchant Center account; submit the feed, check item diagnostics, identifiers, image policy, tax/shipping compliance and actual acceptance.
7. Check that search and AI crawlers can reach the allowed public HTML/API content while checkout, private orders, credentials and admin remain protected.
8. Measure indexed pages, impressions, clicks, product approvals and *attributable* referral traffic. Do not fabricate "AI ranking scores."

## Local regression and inspection

Run only the isolated suite:

    bash scripts/full_regression.sh

SEO unit tests are in tests/seo.php and HTTP smoke checks are in tests/seo_http.sh. Both run automatically inside the disposable isolated workspace. Do not run regression files directly against staging or real data.

Admin: /admin/seo
Public: /sitemap.xml, /robots.txt, /llms.txt,
        /feeds/knowledge.json, /feeds/ai-products.json,
        /feeds/google-merchant.xml

For sources and content authority, CMS authors should reference actual product specifications, craftsmanship processes, warranties, materials and care information already published by Atelier Wood. Nothing in this implementation fabricates external endorsements, reviews or citations.
