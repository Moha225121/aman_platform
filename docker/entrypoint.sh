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

# DigitalOcean may expose an attached database as DATABASE_URL. Laravel uses DB_URL.
if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
    export DB_URL="$DATABASE_URL"
    export DB_CONNECTION=pgsql
fi

# An unresolved App Platform binding such as {db.DATABASE_URL} is not a URL.
# Do not let it stop the container before the web server starts.
if [ "${DB_CONNECTION:-}" = "pgsql" ]; then
    case "${DB_URL:-}" in
        postgres://*|postgresql://*) ;;
        *)
            echo "WARNING: PostgreSQL is selected but DB_URL is missing or unresolved; using temporary SQLite."
            unset DB_URL DATABASE_URL
            export DB_CONNECTION=sqlite
            ;;
    esac
fi

# Allow a fresh App Platform service to boot before a managed database is attached.
# SQLite is only a temporary fallback because the container filesystem is ephemeral.
if [ -z "${DB_CONNECTION:-}" ] || [ "${DB_CONNECTION}" = "sqlite" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    mkdir -p /var/www/html/database
    touch "$DB_DATABASE"
    chown www-data:www-data "$DB_DATABASE" /var/www/html/database
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
