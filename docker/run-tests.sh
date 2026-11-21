#!/bin/sh
# Runs the test suite inside the app container without touching MySQL.
# docker-compose environment variables override phpunit.xml, so reset them here.
set -e
export APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: \
       SESSION_DRIVER=array CACHE_STORE=array MAIL_MAILER=array QUEUE_CONNECTION=sync
exec php vendor/bin/phpunit "$@"
