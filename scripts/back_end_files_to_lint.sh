#!/bin/bash

# Check if the Back end has been changed since the last deployment in Envoyer.

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
    FILENAMES=$(rsync -vrcn --info=misc0,stats0,skip0,flist1 "${NEW_DIR}app/" "${CHECKPOINT_DIR}app/" | tail -n +2 | sed "~s~\b~${NEW_DIR}app/~")

    FILENAMES="$FILENAMES $(rsync -vrcn --info=misc0,stats0,skip0,flist1 "${NEW_DIR}modules/" "${CHECKPOINT_DIR}modules/" | tail -n +2 | sed "~s~\b~${NEW_DIR}modules/~")"

    FILENAMES="$FILENAMES $(rsync -vrcn --info=misc0,stats0,skip0,flist1 "${NEW_DIR}database/" "${CHECKPOINT_DIR}database/" | tail -n +2 | sed "~s~\b~${NEW_DIR}database/~")"

    FILENAMES="$FILENAMES $(rsync -vrcn --info=misc0,stats0,skip0,flist1 "${NEW_DIR}config/" "${CHECKPOINT_DIR}config/" | tail -n +2 | sed "~s~\b~${NEW_DIR}config/~")"

    echo $FILENAMES;
fi
