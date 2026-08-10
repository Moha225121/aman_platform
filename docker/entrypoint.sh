#!/bin/sh
set -e

APP_PORT="${PORT:-8080}"
export APP_ENV=production
export APP_DEBUG=false
cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:${APP_PORT}>
    ServerName localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    ErrorLog /proc/self/fd/2
    CustomLog /proc/self/fd/1 combined
</VirtualHost>
EOF
printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null
sed -i "s/^Listen .*/Listen ${APP_PORT}/" /etc/apache2/ports.conf

# Values pasted into App Platform can retain invisible line breaks. Normalize
# URI environment variables before Laravel or Symfony attempts to parse them.
if [ -n "${APP_URL:-}" ]; then
    APP_URL="$(printf '%s' "$APP_URL" | tr -d '\r\n\t')"
    export APP_URL
fi
if [ -n "${DATABASE_URL:-}" ]; then
    DATABASE_URL="$(printf '%s' "$DATABASE_URL" | tr -d '\r\n\t')"
    export DATABASE_URL
fi
if [ -n "${DB_URL:-}" ]; then
    DB_URL="$(printf '%s' "$DB_URL" | tr -d '\r\n\t')"
    export DB_URL
fi

# DigitalOcean may expose an attached database as DATABASE_URL. Laravel uses DB_URL.
if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
    export DB_URL="$DATABASE_URL"
    export DB_CONNECTION=pgsql
fi

# Production must never silently fall back to container-local SQLite: its users and
# sessions disappear on the next deployment. DigitalOcean can provide either a URL
# or discrete connection parameters, so accept both binding formats.
if [ "${DB_CONNECTION:-}" = "pgsql" ]; then
    if [ -n "${DB_HOST:-}" ] && [ -n "${DB_PORT:-}" ] && \
       [ -n "${DB_DATABASE:-}" ] && [ -n "${DB_USERNAME:-}" ] && \
       [ -n "${DB_PASSWORD:-}" ]; then
        # Prefer discrete fields. This also avoids URL parsing failures when a
        # generated URI contains encoded or invisible control characters.
        unset DB_URL DATABASE_URL
    else
        case "${DB_URL:-}" in
            postgres://*|postgresql://*) ;;
            *)
                echo "ERROR: PostgreSQL connection variables are missing or unresolved." >&2
                echo "Attach the DigitalOcean database component as 'db' and expose its bindable variables." >&2
                exit 1
                ;;
        esac
    fi
fi

# Allow a fresh App Platform service to boot before a managed database is attached.
# SQLite is only a temporary fallback because the container filesystem is ephemeral.
if [ -z "${DB_CONNECTION:-}" ] || [ "${DB_CONNECTION}" = "sqlite" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    export SESSION_DRIVER=cookie
    export CACHE_STORE=file
    export QUEUE_CONNECTION=sync
    mkdir -p /var/www/html/database
    touch "$DB_DATABASE"
    chown -R www-data:www-data "$DB_DATABASE" /var/www/html/database /var/www/html/storage
fi

php artisan migrate --force
if [ "${AMAN_SEED_DATABASE:-true}" = "true" ]; then
    php artisan db:seed --class=DatabaseSeeder --force
    php artisan db:seed --class=KnowledgeBaseSeeder --force
    php artisan db:seed --class=CounselorAccountsSeeder --force
fi
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
