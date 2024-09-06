#!/bin/bash

# Copy build files from the checkpoint directory and update the reference to the
# new directory.

# This script takes one parameter:
# $1 - The new directory to build.
NEW_DIR="$1"

[[ "${NEW_DIR}" != */ ]] && NEW_DIR="${NEW_DIR}/"

CHECKPOINT_FILENAME="last_deployment"
CHECKPOINT_FILE="$HOME/.cache/$CHECKPOINT_FILENAME"

# Check checkpoint file for dir exists and the dir exists.
if test -f "$CHECKPOINT_FILE" &&  [[ -d "$(head -n 1 "$CHECKPOINT_FILE")" ]]
then
    CHECKPOINT_DIR=$(head -n 1 "$CHECKPOINT_FILE")

    [ ! -d "${NEW_DIR}public/assets" ] && cp -r "${CHECKPOINT_DIR}public/assets" "${NEW_DIR}public/"
    [ ! -d "${NEW_DIR}public/templates" ] && cp -r "${CHECKPOINT_DIR}public/templates" "${NEW_DIR}public/"
    [ ! -d "${NEW_DIR}public/cookie-banner" ] && cp -r "${CHECKPOINT_DIR}public/cookie-banner" "${NEW_DIR}public/"
    [ ! -f "${NEW_DIR}resources/views/index.blade.php" ] && cp "${CHECKPOINT_DIR}resources/views/index.blade.php" "${NEW_DIR}resources/views/"
    exit 0
fi
