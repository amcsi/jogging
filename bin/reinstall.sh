#!/usr/bin/env bash

php artisan migrate:refresh --seed
php artisan passport:client --password --no-interaction
