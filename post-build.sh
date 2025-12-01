#!/bin/bash
# Копируем manifest после сборки Vite
cp public/build/.vite/manifest.json public/build/manifest.json
echo "Manifest copied to public/build/manifest.json"
