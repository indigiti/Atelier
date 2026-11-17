#!/usr/bin/env bash
set -euo pipefail
test_root="$(pwd -P)"
if [[ "$ATELIER_TEST_ISOLATED" != "1" || "$ATELIER_TEST_ROOT" != "$test_root" || ! -f .atelier-test-sandbox || -d .git ]]; then
  echo "Refusing HTTP smoke outside the disposable regression workspace." >&2
  exit 78
fi
work="$(mktemp -d)"
port="19731"
export APP_ENV=staging
export ATELIER_DATA_KEY="$(php -r 'echo base64_encode(str_repeat("S",32));')"
export ATELIER_SANDBOX_WEBHOOK_SECRET="http-seo-smoke-sandbox"
# The server is launched only against the isolated test copy, not staging/live.
php -S "127.0.0.1:$port" router.php > "$work/php-server.log" 2>&1 &
server_pid=$!
cleanup() {
  kill "$server_pid" 2>/dev/null || true
  wait "$server_pid" 2>/dev/null || true
  rm -rf -- "$work"
}
trap cleanup EXIT HUP INT TERM
base="http://127.0.0.1:$port"
curl --retry 12 --retry-connrefused --retry-delay 1 -fsS -D "$work/home.headers" "$base/" -o "$work/home.html"
grep -qi '^X-Robots-Tag: noindex' "$work/home.headers"
grep -q 'name="robots" content="noindex' "$work/home.html"
grep -q '"@type":"Organization"' "$work/home.html"
grep -q '<link rel="canonical" href="http://127.0.0.1:' "$work/home.html"

slug="$(php -r '$r=json_decode(file_get_contents("storage/public-domain/catalog/products.json"),true,512,JSON_THROW_ON_ERROR); echo $r[0]["slug"];')"
curl -fsS -c "$work/wishlist.cookies" "$base/product/$slug" -o "$work/product.html"
grep -q '"@type":"Product"' "$work/product.html"
grep -q '"@type":"Offer"' "$work/product.html"
grep -q '"@type":"BreadcrumbList"' "$work/product.html"
grep -q 'property="og:image"' "$work/product.html"
grep -q 'data-wishlist-heart' "$work/product.html"
grep -q '♡ Save to wishlist' "$work/product.html"
csrf="$(php -r 'preg_match("/name=\x22_csrf\x22 value=\x22([^\x22]+)/",file_get_contents($argv[1]),$m); echo $m[1]??"";' "$work/product.html")"
test -n "$csrf"
toggle_wishlist() {
  curl -fsS -b "$work/wishlist.cookies" -H 'Accept: application/json' \
    --data-urlencode "_csrf=$csrf" --data-urlencode "slug=$slug" "$base/wishlist/toggle"
}
test "$(toggle_wishlist)" = '{"ok":true,"saved":true}'
curl -fsS -b "$work/wishlist.cookies" "$base/wishlist" -o "$work/wishlist.html"
grep -q 'data-wishlist-count>1 item' "$work/wishlist.html"
grep -q 'aria-pressed="true" data-wishlist-heart' "$work/wishlist.html"
curl -fsS -b "$work/wishlist.cookies" "$base/product/$slug" -o "$work/saved-product.html"
grep -q 'data-wishlist-inline aria-pressed="true">♥ Saved' "$work/saved-product.html"
test "$(toggle_wishlist)" = '{"ok":true,"saved":false}'
curl -fsS -b "$work/wishlist.cookies" "$base/wishlist" -o "$work/wishlist-empty.html"
grep -q 'data-wishlist-count>0 items' "$work/wishlist-empty.html"
test "$(curl -sS -o /dev/null -w '%{http_code}' -b "$work/wishlist.cookies" -d "slug=$slug" "$base/wishlist/toggle")" = 419
curl -fsS -b "$work/wishlist.cookies" -D "$work/wishlist-redirect.headers" -o /dev/null \
  --data-urlencode "_csrf=$csrf" --data-urlencode "slug=$slug" --data-urlencode "back=/shop?q=shelf" "$base/wishlist/toggle"
grep -Fqi 'Location: /shop?q=shelf' "$work/wishlist-redirect.headers"

curl -fsS "$base/shop?q=shelf" -o "$work/shop.html"
grep -q 'rel="canonical" href="' "$work/shop.html"
if grep -q 'canonical" href="[^"]*[?]q=' "$work/shop.html"; then
  echo "Search query leaked into canonical." >&2; exit 1
fi
curl -fsS "$base/robots.txt" -o "$work/robots.txt"
if grep -q '^Sitemap:' "$work/robots.txt"; then
  echo "Staging advertised indexable sitemap." >&2; exit 1
fi

curl -fsS "$base/sitemap.xml" -o "$work/sitemap.xml"
curl -fsS "$base/feeds/google-merchant.xml" -o "$work/merchant.xml"
php -r 'foreach(array_slice($argv,1) as $f){libxml_use_internal_errors(true);if(simplexml_load_file($f)===false){fwrite(STDERR,"Invalid XML: $f\n");exit(1);}}' "$work/sitemap.xml" "$work/merchant.xml"

curl -fsS "$base/feeds/knowledge.json" -o "$work/knowledge.json"
php -r '$d=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);if(($d["@context"]??"")!=="https://schema.org" || empty($d["@graph"]))exit(1);' "$work/knowledge.json"
curl -fsS "$base/llms.txt" -o "$work/llms.txt"
grep -q '/feeds/knowledge.json' "$work/llms.txt"

test "$(curl -sS -D "$work/ops.headers" -o "$work/ops.json" -w '%{http_code}' "$base/admin/dashboard.json")" = 401
grep -qi '^Cache-Control:.*no-store' "$work/ops.headers"
grep -q 'Sign-in required' "$work/ops.json"

curl -fsS -D "$work/private.headers" "$base/account/login" -o "$work/private.html"
grep -qi '^X-Robots-Tag: noindex' "$work/private.headers"

echo "PASS: HTTP staging noindex, canonical, product Offer/breadcrumb JSON-LD, robots, XML feeds and AI entity endpoints."
