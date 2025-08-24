# Imagem base com PHP 8.3 e Swoole
FROM php:8.3-cli-alpine

# Define o diretório de trabalho
WORKDIR /var/www

# Instala dependências do sistema e extensões PHP
RUN apk add --no-cache \
    bash \
    tzdata \
    curl \
    git \
    libzip-dev \
    mariadb-dev \
    oniguruma-dev \
    autoconf \
    build-base \
    linux-headers \
    brotli-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    sockets \
    pcntl \
    && pecl install swoole redis \
    && docker-php-ext-enable swoole redis


# Configura o timezone
ENV TZ=America/Sao_Paulo
RUN cp /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Instala o Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copia os arquivos do projeto
COPY api/composer.json api/composer.lock ./
RUN composer install --optimize-autoloader --prefer-dist

# Copia o restante do código do projeto
COPY api/ .

# Expoe a porta padrão do Hyperf
EXPOSE 9501

COPY api/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]