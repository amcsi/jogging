#!/bin/bash
set -euo pipefail

php artisan migrate --force

exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=1215
