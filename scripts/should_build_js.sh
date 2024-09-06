#!/bin/bash

# Check if the JavaScript has been changed since the last deployment in Envoyer.
# If no then copy the compiled JS from the previous deployment. Otherwise build
# it.

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

    # Find all different files between the two directories:
    # DO NOT REMOVE: "-n" - It makes sure it's a dry run so it won't DESTROY YOUR DIRECTORIES!!!!!!
    # All params:
    # -v = verbosity
    # -r = recursive
    # -c = runs a checksum on the files rather than comparing lines (should keep it fast)
    # -n = dry-run
    # --info = Only print out the file information
    # tail = The first line of rsync is always there and should be removed
    FILENAMES=$(rsync -vrcn --info=misc0,stats0,skip0,flist1 --exclude=node_modules --exclude=.env "${NEW_DIR}frontend/" "${CHECKPOINT_DIR}frontend/" | tail -n +2)

    # Exit with an error if there are no changes to the front end files
    [[ -z $FILENAMES ]] && exit 1
fi

exit 0
