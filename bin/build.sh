#!/bin/bash

composer install --optimize-autoloader
php artisan cache:clear
npm install
npm run production
