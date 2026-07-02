#!/usr/bin/env sh
set -e

RELEASE_DIR=./release
BUILDS_DIR=./builds

# Last tag version
LAST_TAG=$(git tag --list | head -n 1)

# Clean build directory before compiling.
if [ -f $BUILDS_DIR ]
then
    rm -rf $BUILDS_DIR
fi
php baker app:build baker.php --build-version=$LAST_TAG
composer bin

if [ ! -f $RELEASE_DIR ]
then
    mkdir -p $RELEASE_DIR
else
    rm -r $RELEASE_DIR/*
fi

for file in $(find "$BUILDS_DIR/build" -type f -perm +111)
do
    target_filename=$(basename $file)
    target_path="$RELEASE_DIR/baker-$LAST_TAG-$target_filename"
    echo "Copying '$file' into '$target_path'"
    cp "$file" "$target_path"
done
