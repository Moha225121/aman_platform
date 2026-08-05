#!/bin/sh
set -e

sed -i "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
