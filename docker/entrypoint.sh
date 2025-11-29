#!/bin/sh
set -e

cd /var/www/html

# Ensure writable dirs (works with bind-mount too)
mkdir -p storage bootstrap/cache
chown -R www:www storage bootstrap/cache || true

run_composer_install() {
  # Reduce parallel downloads to avoid tmp zip issues on volumes
  composer config -g max-parallel-http 1 >/dev/null 2>&1 || true

  composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader
}

run_composer_install_fallback_source() {
  composer install \
    --no-interaction \
    --prefer-source \
    --optimize-autoloader
}

if [ ! -f "vendor/autoload.php" ]; then
  echo "[entrypoint] vendor/ missing -> running composer install..."

  # 1st try (dist)
  if ! run_composer_install; then
    echo "[entrypoint] composer install failed (dist). Cleaning tmp/cache and retrying..."

    # cleanup common tmp leftovers
    rm -rf /var/www/html/vendor/composer/tmp-* || true
    composer clear-cache || true

    # 2nd try (dist again)
    if ! run_composer_install; then
      echo "[entrypoint] composer install failed again. Falling back to --prefer-source..."
      rm -rf /var/www/html/vendor/composer/tmp-* || true
      composer clear-cache || true
      run_composer_install_fallback_source
    fi
  fi

  echo "[entrypoint] composer install done."
fi

exec "$@"
