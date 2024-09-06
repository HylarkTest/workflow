#!/bin/bash

# An added security measure is to include subresource integrity (SRI) hashes in the HTML
# as outlined here: https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity
# This script will add the SRI hashes for stripe and fontawesome to the HTML files in the public directory.

URLS=(
    "https://js.stripe.com/v3"
    "https://kit.fontawesome.com/93ed051d4d.js"
)

# Use gsed on Mac
if [[ "$OSTYPE" == "darwin"* ]]; then
    SED_COMMAND=gsed
else
    SED_COMMAND=sed
fi

for URL in "${URLS[@]}" ; do
    # Get project directory
    PROJECT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." >/dev/null 2>&1 && pwd )"
    # Array of files to add the SRI hash to
    FILES=(
        "$PROJECT_DIR/resources/views/index.blade.php"
        "$PROJECT_DIR/resources/views/templates-index.blade.php"
        "$PROJECT_DIR/resources/views/cookie-banner-index.blade.php"
        "$PROJECT_DIR/frontend/public/index.html"
        "$PROJECT_DIR/frontend/public/templates-index.html"
        "$PROJECT_DIR/frontend/public/cookie-banner-index.html"
    )

    # Loop through the files and add the SRI hash
    for FILE in "${FILES[@]}" ; do
        if [[ ! -f "$FILE" ]]; then
            continue
        fi
        $SED_COMMAND -i -E "s~<script src=\"$URL\"( integrity=\"[^\"]+\")?~<script src=\"$URL\"~" "$FILE"
    done
done
