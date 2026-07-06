FROM docker.io/library/composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-progress --prefer-dist

FROM docker.io/library/node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM docker.io/library/alpine:3.21

RUN apk add --no-cache \
	php84 \
	php84-fpm \
	php84-bcmath \
	php84-ctype \
	php84-curl \
	php84-dom \
	php84-fileinfo \
	php84-mbstring \
	php84-openssl \
	php84-pdo \
	php84-pdo_sqlite \
	php84-phar \
	php84-session \
	php84-sqlite3 \
	php84-tokenizer \
	php84-xml \
	php84-xmlwriter \
	php84-simplexml \
	caddy \
	supervisor \
	cups-client \
	samba-common-tools \
	sane-utils \
	tzdata

RUN ln -sf /usr/bin/php84 /usr/bin/php

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R nobody:nobody storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
