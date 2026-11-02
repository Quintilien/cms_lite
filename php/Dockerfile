# CMS Lite 4 — PHP 8.3 + Apache (like OscarDB): a small self-contained web server
# per container; the VPS's Caddy passes requests to it over HTTP (reverse_proxy).
# Same image for the public site and the editor.
FROM php:8.3-apache

# GD (image resizing) and EXIF (orientation of phone photos).
# pdo_sqlite, dom and fileinfo are already in the official image.
RUN apt-get update \
 && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" gd exif \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod headers

COPY php.ini    /usr/local/etc/php/conf.d/zz-cms.ini
COPY apache.conf /etc/apache2/conf-enabled/zz-cms.conf
