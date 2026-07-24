FROM php:8.3-fpm

# Dependências do sistema + extensões PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        bcmath \
        opcache \
        pcntl \
        sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-devmind.ini

# Usuário espelhando o UID do host (evita dor de cabeça com permissões)
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} devmind || true \
    && useradd -u ${UID} -g ${GID} -m devmind

WORKDIR /var/www/html
USER devmind

CMD ["php-fpm"]