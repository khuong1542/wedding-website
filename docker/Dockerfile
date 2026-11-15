FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" mysqli pdo_mysql mbstring dom xml zip \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
       > /etc/apache2/conf-available/app.conf \
    && a2enconf app

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
