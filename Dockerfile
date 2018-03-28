FROM php:7.2-apache
MAINTAINER  Attila Szeremi <attila+webdev@szeremi.com>
WORKDIR /var/www
RUN cd /var/www

RUN apt-get update && apt-get install -y \
  # For installing node
  curl \
  wget \
  gnupg \

  # For composer
  zlib1g-dev

RUN curl -sL https://deb.nodesource.com/setup_9.x | bash - && \
  apt-get update && \
  apt-get install -y nodejs && \
  node --version && \
  npm --version

# This includes the docker-php-pecl-install executable
COPY bin/docker-php-pecl-install /usr/local/bin/

# PHP extensions
RUN docker-php-ext-install \
  zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

COPY composer.json .
COPY composer.lock .

# These directories need to exist if we composer install without hooks/scripts.
RUN mkdir -p database/seeds
RUN mkdir -p database/factories

# Do not run hooks, because they require the project files to already be there.
# We want to be able to avoid complete (slow) composer installs in the Dockerfile with caching if possible.
RUN composer install --no-scripts

COPY package.json .
COPY package-lock.json .
RUN npm install

COPY resources/assets resources/assets
COPY webpack.mix.js .
RUN npm run production

COPY . .

RUN mkdir -p bootstrap/cache && chmod a+rwx bootstrap/cache

# This time, optimize and run hooks as well.
RUN composer install --optimize-autoloader

COPY config/docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

RUN npm run production

RUN mkdir -p storage && chmod -R a+rwx storage
# The parent directory of the sqlite database must be writable.
# https://github.com/wallabag/wallabag/issues/1845#issuecomment-205726683
RUN chmod a+rw database/

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
