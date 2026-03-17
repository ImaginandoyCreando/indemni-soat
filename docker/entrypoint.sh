#!/bin/sh
set -e
cd /var/www/html

mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Primero migrar para que existan las tablas
php artisan migrate --force

# Luego limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Seeder
php artisan db:seed --class=AdminUserSeeder --force

php artisan storage:link || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

mkdir -p /var/log/supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
