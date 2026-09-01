FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

# System packages: PHP 8.3 CLI + the extensions this Laravel app needs
# (sqlite3/pdo_sqlite for the DB, gd for intervention/image, zip for
# maatwebsite/excel, mbstring/xml/bcmath/intl/gmp/curl for the framework),
# Composer, and Node.js 22 for the Vite/Tailwind frontend.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl git unzip gnupg \
        php8.3-cli \
        php8.3-mbstring \
        php8.3-xml \
        php8.3-curl \
        php8.3-zip \
        php8.3-sqlite3 \
        php8.3-gd \
        php8.3-bcmath \
        php8.3-intl \
        php8.3-gmp \
    && curl -sS https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

USER ubuntu
