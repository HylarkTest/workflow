#! /bin/bash

# Create a supervisor for a nod script that will be started when the VM is booted.

name="imgproxy"

PROJECT_DIRECTORY="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." &> /dev/null && pwd )"

cd "${PROJECT_DIRECTORY}/scripts"
yarn install

project=$(basename "$PROJECT_DIRECTORY")

process="$project-$name"

echo "Configuring $project $name supervisor"
if [[ ! -f "/etc/supervisor/conf.d/$process.conf" ]];
then
    sudo touch "/etc/supervisor/conf.d/$process.conf"
fi
echo "
[program:$process]
process_name=%(program_name)s
command=node $PROJECT_DIRECTORY/scripts/imgProxy.js
autostart=true
autorestart=true
user=vagrant
redirect_stderr=true
stdout_logfile=$PROJECT_DIRECTORY/storage/logs/$name.log
stopwaitsecs=3600
" | sudo tee "/etc/supervisor/conf.d/$process.conf" > /dev/null

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl stop "$process"
sudo supervisorctl start "$process"
