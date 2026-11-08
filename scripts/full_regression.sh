#!/usr/bin/env bash
set -euo pipefail
source_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
test_root="$(mktemp -d "${TMPDIR:-/tmp}/atelier-regression.XXXXXXXX")"
cleanup() { rm -rf -- "$test_root"; }
trap cleanup EXIT HUP INT TERM
for entry in config public python scripts src templates tests router.php nginx.sample.conf; do
  cp -R "$source_root/$entry" "$test_root/$entry"
done
# Copy *only* version-controlled public catalogue/fixtures. Never copy private application data.
mkdir -p "$test_root/storage/public-domain" "$test_root/storage/indexes" "$test_root/storage/journals" "$test_root/storage/revisions/catalog" "$test_root/storage/backups" "$test_root/storage/tmp" "$test_root/storage/private-domain"
cp -R "$source_root/storage/public-domain/." "$test_root/storage/public-domain/"
touch "$test_root/.atelier-test-sandbox"
export ATELIER_TEST_ISOLATED=1
export ATELIER_TEST_ROOT="$test_root"
cd "$test_root"
find . -name "*.php" -print0 | xargs -0 -n1 php -l >/dev/null
for t in foundation_hardening smoke commerce_core configurator bespoke makeos procurement quality fulfilment cms designos sacred_spaces themes mediaos intelligence passport tradeos integrations security enquiries commerce_ux release_regression; do
  echo "== $t =="
  php "tests/$t.php"
done
echo "PASS: disposable isolated regression suite"
