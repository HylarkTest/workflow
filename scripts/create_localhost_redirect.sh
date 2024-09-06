#!/usr/bin/env bash
# This script will generate a new site rule in nginx to redirect a call to
# localhost from a specified port to a specified url.
# Some integrations only allow testing domains to be localhost so this allows
# the developer to use localhost and have that point to the custom development
# site.

PORT="$1";
REDIRECT="$2";
FILENAME="localhost_$PORT";

block="server {
    listen ${PORT};
    server_name localhost;
    return 301 ${REDIRECT}\$request_uri;
}
"

echo "$block" > "/etc/nginx/sites-available/$FILENAME"
ln -fs "/etc/nginx/sites-available/$FILENAME" "/etc/nginx/sites-enabled/$FILENAME"
