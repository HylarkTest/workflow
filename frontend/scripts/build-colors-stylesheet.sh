#! /bin/bash

find "../public/css/" -maxdepth 1 -name "colors.*.css" -delete

COLOR_FILENAME="colors.$(date "+%Y%m%d%H%M").css"

FRONTEND_DIRECTORY="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." &> /dev/null && pwd )"

yarn tailwindcss --input="$FRONTEND_DIRECTORY/src/style/colors.css" --output="$FRONTEND_DIRECTORY/../public/css/$COLOR_FILENAME" --minify

# Use gsed on Mac
if [[ "$OSTYPE" == "darwin"* ]]; then
    SED_COMMAND=gsed
else
    SED_COMMAND=sed
fi

$SED_COMMAND -i "s/colors\.[0-9]\{12\}\.css/$COLOR_FILENAME/" "$FRONTEND_DIRECTORY/index.html"
