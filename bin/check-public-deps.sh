#!/usr/bin/env bash

# Fails if composer.json references a private repository or requires a package
# that cannot be resolved from Packagist + repo.magento.com.

set -uo pipefail

cd "$(dirname "$0")/.."

fail=0

grep -q 'gitlab\.hyva\.io' composer.json && {
  echo "FAIL: composer.json references gitlab.hyva.io"
  fail=1
}

grep -q 'git\.nfq\.asia' composer.json && {
  echo "FAIL: composer.json references git.nfq.asia"
  fail=1
}

python3 - <<'PY' || fail=1
import json
import sys

data = json.load(open("composer.json"))
bad = [
    name
    for section in ("require", "require-dev")
    for name in data.get(section, {})
    if name.startswith("hyva-themes/")
]
if bad:
    print("FAIL: hyva-themes package in require/require-dev: " + ", ".join(bad))
    sys.exit(1)
PY

composer validate --no-check-publish || fail=1

exit "$fail"
