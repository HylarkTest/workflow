#! /bin/bash
site=$1
port=$2
export OCTANE_STATE_FILE="/home/forge/$site/storage/logs/octane-server-state-$port.json"
php artisan octane:start --port="$port"
