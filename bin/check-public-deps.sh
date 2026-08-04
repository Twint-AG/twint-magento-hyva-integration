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

def repository_entries(data):
    """Normalise composer.json's `repositories` to a list of (key, value) pairs.

    It may be the object form (`{"name": {...}}`) or the array form
    (`[{...}, {...}]`); the array form has no per-entry key, so the index is
    used instead.
    """
    repos = data.get("repositories", {})
    if isinstance(repos, dict):
        return list(repos.items())
    if isinstance(repos, list):
        return [(str(index), repo) for index, repo in enumerate(repos)]
    print(
        "FAIL: composer.json repositories is neither an object nor an array: "
        + type(repos).__name__
    )
    return []

repo_entries = repository_entries(data)

bad = [
    name
    for section in ("require", "require-dev")
    for name in data.get(section, {})
    if name.startswith("hyva-themes/")
] + [
    key
    for key, repo in repo_entries
    if isinstance(repo, dict) and key.startswith("hyva-themes/")
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

for key, repo in repo_entries:
    if not isinstance(repo, dict):
        continue
    url = repo.get("url")
    host = urlparse(url).hostname if url else None
    if not is_allowed_host(host):
        print(f"FAIL: composer.json repositories.{key} points at disallowed URL: {url}")
        failed = True

stability = data.get("minimum-stability", "stable")
if stability != "stable":
    print(f"FAIL: composer.json minimum-stability is '{stability}', expected 'stable'")
    failed = True

for name, constraint in data.get("require", {}).items():
    if isinstance(constraint, str) and ("dev-" in constraint or constraint.endswith("-dev")):
        print(f"FAIL: composer.json require.{name} uses a dev constraint: {constraint}")
        failed = True

if failed:
    sys.exit(1)
PY

composer validate --no-check-publish || fail=1

exit "$fail"
