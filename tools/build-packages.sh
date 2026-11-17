#!/usr/bin/env bash
# Builds the two shippable packages from the source in this repository:
#   dist/moon-theme-final.zip
#   dist/moon-demo-importer-final.zip
#
# The sandbox host has no `zip` binary, so the packaging runs inside the
# WordPress CLI image that the compose stack already caches.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

docker run --rm -u 0:0 \
  -v "$root":/src \
  -w /src \
  --entrypoint php \
  wordpress:cli-php8.3 tools/package.php
