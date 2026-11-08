#!/usr/bin/env bash
set -euo pipefail

# Only immutable code and first-run seeds. Live state is NEVER packaged.
root="$(cd "$(dirname "$0")/.." && pwd -P)"
out="$root/build/digiops"
mkdir -p "$out"
rm -rf -- "$out/release"
pub="$out/release/public"
priv="$out/release/private/app"
mkdir -p "$pub/assets" "$priv/public" "$priv/storage/public-domain"

# Public payload: entrypoint, rewrite rules and immutable static assets.
cp -R "$root/public/assets/." "$pub/assets/"
cp "$root/public/.htaccess" "$pub/.htaccess"

# Private code, HTML templates, worker scripts and first-run catalogue seeds.
for dir in config src templates python scripts; do
  cp -R "$root/$dir" "$priv/$dir"
done
cp "$root/public/index.php" "$priv/public/index.php"
cp -R "$root/storage/public-domain/." "$priv/storage/public-domain/"
cp "$root/SEO_GEO_READINESS.md" "$priv/SEO_GEO_READINESS.md"

cat > "$pub/index.php" <<'PHP'
<?php
declare(strict_types=1);
/**
 * DigiOps split entrypoint.
 * Public:  public_html/<application>/
 * Private: private_html/<application>/app/
 * State:   private_html/<application>/storage/
 */
$application=basename(__DIR__);
if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,99}$/i', $application)) {
    http_response_code(503);
    exit('Application location is invalid.');
}
$home=dirname(__DIR__,2);
$privateRoot=$home.'/private_html/'.$application;
$entry=$privateRoot.'/app/public/index.php';
if (!is_file($entry) || !is_readable($entry)) {
    http_response_code(503);
    exit('The application has not been activated.');
}
putenv('ATELIER_STORAGE_ROOT='.$privateRoot.'/storage');
putenv('ATELIER_PUBLIC_ROOT='.__DIR__);
require $entry;
PHP

revision="$(printenv GITHUB_SHA || printf 'local')"
cat > "$out/release/RELEASE.json" <<JSON
{"name":"Atelier","format":"digiops-split-v1","target":"staging","source":"indigiti/Atelier","commit":"$revision","production_ready":false}
JSON
echo "DigiOps staging release prepared: $out/release"
