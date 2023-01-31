
# Use the official PHP image.
# https://hub.docker.com/_/php
FROM php:8.1-apache

# Configure PHP for Cloud Run.
# Precompile PHP code with opcache.
RUN a2enmod rewrite

RUN apt-get update && \
    apt-get install -y --no-install-recommends acl libssl-dev zlib1g-dev curl git unzip netcat libxml2-dev libpq-dev libzip-dev && \
    pecl install apcu && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) zip opcache intl pdo_pgsql pgsql && \
    docker-php-ext-enable apcu pdo_pgsql sodium && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN set -ex; \
  { \
    echo "; Cloud Run enforces memory & timeouts"; \
    echo "memory_limit = -1"; \
    echo "max_execution_time = 0"; \
    echo "; File upload at Cloud Run network limit"; \
    echo "upload_max_filesize = 32M"; \
    echo "post_max_size = 32M"; \
    echo "; Configure Opcache for Containers"; \
    echo "opcache.enable = On"; \
    echo "opcache.validate_timestamps = Off"; \
    echo "; Configure Opcache Memory (Application-specific)"; \
    echo "opcache.memory_consumption = 32"; \
  } > "$PHP_INI_DIR/conf.d/cloud-run.ini"

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"


WORKDIR /var/www

# build for production
ARG APP_ENV=prod

COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-scripts --no-progress; \
	composer clear-cache

COPY bin bin/
COPY config config/
COPY migrations migrations/
COPY public public/
COPY src src/
COPY templates templates/

ARG TRUSTED_PROXIES \
    TRUSTED_HOSTS \
    APP_SECRET \
    DB_NAME \
    DB_HOST \
    DB_PORT \
    DB_USER \
    DB_PASSWORD \
    DB_VERSION \
    DB_CHARSET \
    MAILER_DSN \
    CORS_ALLOW_ORIGIN \
    MERCURE_URL \
    MERCURE_PUBLIC_URL \
    MERCURE_JWT_SECRET \
    JWT_PASSPHRASE \
    STRIPE_PK \
    STRIPE_SK \
    STRIPE_WH_SK \
    FRONT_URL \
    KYC_API_URL \
    KYC_API_SECRET \
    HTTPS_PROXY=on

ENV TRUSTED_PROXIES=$TRUSTED_PROXIES \
    TRUSTED_HOSTS=$TRUSTED_HOSTS \
    APP_ENV=$APP_ENV \
    APP_SECRET=$APP_SECRET \
    DB_NAME=$DB_NAME \
    DB_HOST=$DB_HOST \
    DB_PORT=$DB_PORT \
    DB_USER=$DB_USER \
    DB_PASSWORD=$DB_PASSWORD \
    DB_VERSION=$DB_VERSION \
    DB_CHARSET=$DB_CHARSET \
    MAILER_DSN=$MAILER_DSN \
    CORS_ALLOW_ORIGIN=$CORS_ALLOW_ORIGIN \
    MERCURE_URL=$MERCURE_URL \
    MERCURE_PUBLIC_URL=$MERCURE_PUBLIC_URL \
    MERCURE_JWT_SECRET=$MERCURE_JWT_SECRET \
    JWT_PASSPHRASE=$JWT_PASSPHRASE \
    STRIPE_PK=$STRIPE_PK \
    STRIPE_SK=$STRIPE_SK \
    STRIPE_WH_SK=$STRIPE_WH_SK \
    FRONT_URL=$FRONT_URL \
    KYC_API_URL=$KYC_API_URL \
    KYC_API_SECRET=$KYC_API_SECRET \
    HTTPS_PROXY=$HTTPS_PROXY

COPY docker/create-dot-env.sh . 
RUN chmod +x create-dot-env.sh; \
	./create-dot-env.sh; \
	rm create-dot-env.sh;

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync

COPY docker/apache.conf /etc/apache2/sites-enabled/000-default.conf
RUN sed -i 's/80/80/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80

CMD ["apache2-foreground"]
ENTRYPOINT ["docker-entrypoint"]