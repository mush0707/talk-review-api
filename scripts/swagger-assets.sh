#!/usr/bin/env sh
set -e

mkdir -p public/vendor/swagger-api/swagger-ui/dist
cp -R vendor/swagger-api/swagger-ui/dist/* public/vendor/swagger-api/swagger-ui/dist/
