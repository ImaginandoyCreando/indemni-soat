FROM php:8.2-fpm-alpine

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

# ─── Configuracion PHP ────────────────────────────────────────────────────────
# output_buffering=Off es CRITICO: evita que PHP acumule el HTML completo
# en memoria antes de enviarlo a nginx. Sin esto, paginas grandes (listas
# con muchas filas) se cortan a mitad — NS_ERROR_NET_PARTIAL_TRANSFER.
RUN echo "memory_limit=512M"        >  /usr/local/etc/php/conf.d/custom.ini \
 && echo "max_execution_time=120"   >> /usr/local/etc/php/conf.d/custom.ini \
 && echo "output_buffering=Off"     >> /usr/local/etc/php/conf.d/custom.ini \
 && echo "zlib.output_compression=Off" >> /usr/local/etc/php/conf.d/custom.ini \
 && echo "implicit_flush=On"        >> /usr/local/etc/php/conf.d/custom.ini

# Forzar los mismos limites en el pool de PHP-FPM
RUN sed -i '/^php_value\[memory_limit\]/d'       /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true \
 && sed -i '/^php_value\[output_buffering\]/d'   /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true \
 && echo "php_value[memory_limit]      = 512M"  >> /usr/local/etc/php-fpm.d/www.conf \
 && echo "php_value[max_execution_time] = 120"  >> /usr/local/etc/php-fpm.d/www.conf \
 && echo "php_admin_value[output_buffering] = Off" >> /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    || composer install --no-dev --optimize-autoloader --no-interaction --prefer-source

RUN npm install && npm run build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# nginx.conf con fastcgi_buffering off — evita corte de respuestas grandes
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
