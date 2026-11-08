#!/usr/bin/env bash
set -euo pipefail
root="$(cd "$(dirname "$0")/.." && pwd -P)"
bash "$root/scripts/package_digiops_release.sh" >/dev/null
payload="$root/build/digiops/release"
tmp="$(mktemp -d)"
trap 'rm -rf -- "$tmp"' EXIT HUP INT TERM

# Verify public/private artifact boundaries (secrets and PHP internals never public).
test -f "$payload/public/index.php"
test -f "$payload/public/.htaccess"
test -d "$payload/public/assets/images/products"
test -f "$payload/private/app/public/index.php"
test -f "$payload/private/app/src/seo.php"
test -f "$payload/private/app/storage/public-domain/catalog/products.json"
test ! -e "$payload/public/src"
test ! -e "$payload/public/storage"
test ! -e "$payload/private/storage"
test ! -e "$payload/private/app/storage/private-domain"
test ! -e "$payload/private/app/.env"

# Simulate the exact DigiOps Cloudways sibling public_html/private_html layout.
home="$tmp/cloudways"
mkdir -p "$home/public_html/atelier" "$home/private_html/atelier"
cp -R "$payload/public/." "$home/public_html/atelier/"
cp -R "$payload/private/." "$home/private_html/atelier/"
wrapper="$home/public_html/atelier/index.php"
key="$(php -r 'echo base64_encode(str_repeat("D",32));')"
request() {
  local route="$1"
  APP_ENV=staging ATELIER_DATA_KEY="$key" ATELIER_SANDBOX_WEBHOOK_SECRET=staging-package-test php -r '
    $_SERVER["SCRIPT_NAME"]="/atelier/index.php";
    $_SERVER["REQUEST_URI"]=$argv[1];
    $_SERVER["REQUEST_METHOD"]="GET";
    $_SERVER["HTTP_HOST"]="stage.example.test";
    $_SERVER["REMOTE_ADDR"]="127.0.0.1";
    require $argv[2];
  ' "/atelier$route" "$wrapper"
}

request "/sitemap.xml" >"$tmp/sitemap.xml"
request "/robots.txt" >"$tmp/robots.txt"
request "/feeds/knowledge.json" >"$tmp/knowledge.json"
request "/feeds/google-merchant.xml" >"$tmp/merchant.xml"
request "/feeds/ai-products.json" >"$tmp/products.json"
request "/" >"$tmp/home.html"
test -f "$home/private_html/atelier/storage/public-domain/catalog/products.json"
test -f "$home/private_html/atelier/storage/public-domain/content/cms.json"
test -d "$home/private_html/atelier/storage/private-domain"
grep -q 'http://stage.example.test/atelier/product/' "$tmp/sitemap.xml"
grep -q 'noindex' "$tmp/home.html"
grep -q 'name="robots" content="noindex' "$tmp/home.html"
if grep -q '^Sitemap:' "$tmp/robots.txt"; then echo "Staging robots advertised indexing sitemap." >&2;exit 1;fi

php -r '
  foreach([$argv[1],$argv[2]] as $path)if(simplexml_load_file($path)===false)exit(1);
  $a=json_decode(file_get_contents($argv[3]),true,512,JSON_THROW_ON_ERROR);
  $b=json_decode(file_get_contents($argv[4]),true,512,JSON_THROW_ON_ERROR);
  if(count($a["@graph"]??[])<30||count($b["products"]??[])<30)exit(1);
' "$tmp/sitemap.xml" "$tmp/merchant.xml" "$tmp/knowledge.json" "$tmp/products.json"

# Simulate a second deployment replacing code. Existing runtime JSON cannot be reset.
printf '%s\n' '{"runtime":"must-survive"}' > "$home/private_html/atelier/storage/public-domain/content/cms.json"
cp -R "$payload/private/." "$home/private_html/atelier/"
request "/sitemap.xml" >"$tmp/again.xml"
grep -q '"runtime":"must-survive"' "$home/private_html/atelier/storage/public-domain/content/cms.json"
test ! -e "$home/public_html/atelier/storage"

echo "PASS: DigiOps split artifact, private data boundary, subdirectory SEO endpoints, and redeploy persistence."
