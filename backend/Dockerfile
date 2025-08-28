FROM php:7.4-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    libzip-dev libicu-dev libonig-dev git unzip \
    && docker-php-ext-install pdo pdo_mysql intl \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Diretório de trabalho
WORKDIR /app

# Composer
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts


# Copia arquivos do backend
COPY . .

EXPOSE 8000
