#! /bin/bash

PROJECT_DIRECTORY="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." &> /dev/null && pwd )"

sudo apt-get install php8.1-imagick libcurl4-openssl-dev

php81

sudo pecl channel-update pecl.php.net
sudo pecl install swoole

echo "
; priority=25
extension=swoole.so
" | sudo tee /etc/php/8.1/cli/conf.d/25-swoole.ini > /dev/null

composer install

cd "$PROJECT_DIRECTORY"

"$PROJECT_DIRECTORY/scripts/create_supervisor.sh" horizon
"$PROJECT_DIRECTORY/scripts/create_supervisor.sh" "octane:start" octane
