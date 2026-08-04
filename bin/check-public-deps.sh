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
from urllib.parse import urlparse

try:
    data = json.load(open("composer.json"))
except json.JSONDecodeError as exc:
    print(f"FAIL: composer.json is not valid JSON: {exc}")
    sys.exit(1)

failed = False

bad = [
    name
    for section in ("require", "require-dev", "repositories")
    for name in data.get(section, {})
    if name.startswith("hyva-themes/")
]
if bad:
    print(
        "FAIL: hyva-themes key in require/require-dev/repositories: "
        + ", ".join(bad)
    )
    failed = True

def is_allowed_host(host):
    if host is None:
        return False
    return host == "repo.magento.com" or host == "packagist.org" or host.endswith(".packagist.org")

for key, repo in data.get("repositories", {}).items():
    if not isinstance(repo, dict):
        continue
    url = repo.get("url")
    host = urlparse(url).hostname if url else None
    if not is_allowed_host(host):
        print(f"FAIL: composer.json repositories.{key} points at disallowed URL: {url}")
        failed = True

if failed:
    sys.exit(1)
PY

composer validate --no-check-publish || fail=1

exit "$fail"
