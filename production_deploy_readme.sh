#!/bin/bash

# Script to copy readme.txt to SVN trunk and commit
# Usage: ./production_deploy_readme.sh

set -e

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
README_FILE="$PLUGIN_DIR/readme.txt"
SVN_TRUNK_DIR="/home/yinon11/talktopc-wp-plugin-dev/talk-to-pc/trunk"

# Check if readme.txt exists
if [ ! -f "$README_FILE" ]; then
    echo "Error: readme.txt not found at $README_FILE"
    exit 1
fi

# Check if SVN trunk directory exists
if [ ! -d "$SVN_TRUNK_DIR" ]; then
    echo "Error: SVN trunk directory not found at $SVN_TRUNK_DIR"
    exit 1
fi

echo "Copying readme.txt to SVN trunk..."
cp "$README_FILE" "$SVN_TRUNK_DIR/readme.txt"

echo "Committing to SVN..."
cd "$SVN_TRUNK_DIR"
svn ci -m "updated readme"

echo "Done! readme.txt has been copied and committed to SVN."
