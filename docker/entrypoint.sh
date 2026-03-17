#!/bin/sh
set -e
cd /var/www/html
# Crear directorios necesarios
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache
# Permisos
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan view:clear
# Ejecutar migraciones
php artisan migrate --force
# Crear usuario admin si no existe
php artisan db:seed --class=AdminUserSeeder --force
# Crear link de storage
php artisan storage:link || true
# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Crear directorios de log para supervisor
mkdir -p /var/log/supervisor
# Iniciar servicios
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
