FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    imap-dev \
    openssl-dev \
    nodejs \
    npm

# Instalar extensiones PHP
RUN docker-php-ext-configure imap --with-imap --with-imap-ssl
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    imap

# Configuracion PHP — memoria y tiempos aumentados para evitar corte de respuesta
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/custom.ini \
 && echo "max_execution_time=120" >> /usr/local/etc/php/conf.d/custom.ini \
 && echo "output_buffering=65536" >> /usr/local/etc/php/conf.d/custom.ini \
 && echo "zlib.output_compression=Off" >> /usr/local/etc/php/conf.d/custom.ini \
 && echo "implicit_flush=Off" >> /usr/local/etc/php/conf.d/custom.ini

# Aplicar memory_limit en el pool de PHP-FPM (www.conf) para que no sea
# anulado por la configuracion del pool que sobreescribe el php.ini global.
RUN sed -i '/^php_value\[memory_limit\]/d' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true \
 && echo "php_value[memory_limit] = 512M" >> /usr/local/etc/php-fpm.d/www.conf \
 && echo "php_value[max_execution_time] = 120" >> /usr/local/etc/php-fpm.d/www.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP (con fallback a --prefer-source si falla la descarga por dist)
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    || composer install --no-dev --optimize-autoloader --no-interaction --prefer-source

# VITE BUILD — Compilar assets CSS/JS para produccion
RUN npm install && npm run build

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuracion Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configuracion Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Script de entrada
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
