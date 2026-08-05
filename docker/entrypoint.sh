#!/bin/sh
set -e

APP_PORT="${PORT:-8080}"
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
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
