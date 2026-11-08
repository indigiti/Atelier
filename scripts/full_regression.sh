#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
find . -name "*.php" -print0 | xargs -0 -n1 php -l >/dev/null
for t in foundation_hardening smoke commerce_core configurator bespoke makeos procurement quality fulfilment cms designos sacred_spaces themes mediaos intelligence passport tradeos integrations security commerce_ux release_regression; do
  echo "== $t =="
  php "tests/$t.php"
done
echo "PASS: full v0.9.8.1 regression suite"
