#!/bin/sh
# Rebuilds the editor: ../editor.js (a single file, no runtime dependency)
set -e
cd "$(dirname "$0")"
[ -d node_modules ] || npm ci
npx esbuild main.js --bundle --minify --format=iife --target=es2020 --outfile=../editor.js
