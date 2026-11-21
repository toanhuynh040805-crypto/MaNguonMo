#!/bin/sh
set -e

# Configuration comes from docker-compose environment variables. Do NOT create
# a .env here: when one exists, "artisan serve" passes only its variables to the
# web server and drops the Docker ones (APP_KEY, DB_*).

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --no-progress
fi

# Wait for MySQL to accept connections.
until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Exception $e) { exit(1); }'; do
    echo "Waiting for MySQL..."
    sleep 2
done

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link 2>/dev/null || true
php artisan optimize:clear

# PHP's built-in server (what "artisan serve" uses) started directly, so it keeps
# every Docker environment variable. Real env vars also win over any host .env.
export PHP_CLI_SERVER_WORKERS=4
# Laravel's router script treats the working directory as the web root.
cd public
exec php -S 0.0.0.0:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
