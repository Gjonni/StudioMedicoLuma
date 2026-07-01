#!/bin/sh
set -e

# Abilita il backend "net" di SANE per raggiungere saned sull'host via rete.
if [ -n "$SANED_HOST" ]; then
	grep -qx net /etc/sane.d/dll.conf || echo net >> /etc/sane.d/dll.conf
	echo "$SANED_HOST" > /etc/sane.d/net.conf
fi

if [ ! -f /var/www/html/database/database.sqlite ]; then
	touch /var/www/html/database/database.sqlite
fi
chown -R nobody:nobody /var/www/html/database

php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan migrate --force
php artisan db:seed --force

exec supervisord -c /etc/supervisord.conf
