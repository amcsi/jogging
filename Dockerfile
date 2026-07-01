# Build frontend assets on a fixed Node 14 image (Laravel Mix 2 / node-sass).
FROM node:14-bullseye-slim AS frontend

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    python3 \
    make \
    g++ \
    && ln -s /usr/bin/python3 /usr/bin/python \
    && rm -rf /var/lib/apt/lists/*

COPY package.json package-lock.json ./
RUN npm ci

COPY resources/assets resources/assets
COPY webpack.mix.js .
RUN mkdir -p public
RUN npm run production \
    && node -e "const fs=require('fs'); fs.writeFileSync('public/mix-manifest.json', JSON.stringify({'/js/app.js':'/js/app.js','/css/app.css':'/css/app.css'}));"

FROM php:8.2
LABEL maintainer="Attila Szeremi <attila+webdev@szeremi.com>"

WORKDIR /var/www

RUN apt-get update && apt-get install -y --no-install-recommends \
    $PHPIZE_DEPS \
    curl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libbrotli-dev \
    libssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

COPY bin/docker-php-pecl-install /usr/local/bin/

RUN docker-php-ext-install zip pcntl

RUN docker-php-pecl-install swoole

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock ./

RUN mkdir -p database/seeders database/factories

RUN composer install --no-scripts --no-interaction --prefer-dist

COPY package.json package-lock.json ./
COPY resources/assets resources/assets
COPY webpack.mix.js ./

COPY --from=frontend /app/public/js public/js
COPY --from=frontend /app/public/css public/css
COPY --from=frontend /app/public/mix-manifest.json public/mix-manifest.json

COPY . .

RUN mkdir -p bootstrap/cache && chmod a+rwx bootstrap/cache

RUN composer install --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/{cache,sessions,views} \
    && chmod -R a+rwx storage/framework/{cache,sessions,views}

RUN chmod a+rw database/

CMD ["bin/start.sh"]
