#!/usr/bin/env bash
set -o errexit

composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=StapNodeSeeder
php artisan db:seed --class=CameraSeeder
php artisan storage:link
