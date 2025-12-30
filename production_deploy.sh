#!/bin/bash

# Script to upgrade plugin version (minor), create zip, and save to Downloads
# Usage: ./build-plugin.sh

set -e

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_FILE="$PLUGIN_DIR/ttp-voice-widget.php"
DEST_DIR="$HOME/Downloads"

# Check if plugin file exists
if [ ! -f "$PLUGIN_FILE" ]; then
    echo "Error: Plugin file not found at $PLUGIN_FILE"
    exit 1
fi

# Extract current version from plugin file
CURRENT_VERSION=$(grep -E "^ \* Version:" "$PLUGIN_FILE" | sed 's/.*Version: //' | tr -d ' ')
if [ -z "$CURRENT_VERSION" ]; then
    echo "Error: Could not find version in plugin file"
    exit 1
fi

echo "Current version: $CURRENT_VERSION"

# Increment minor version (e.g., 1.9.1 -> 1.9.2)
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR="${VERSION_PARTS[0]}"
MINOR="${VERSION_PARTS[1]}"
PATCH="${VERSION_PARTS[2]}"

# Increment patch version (minor upgrade)
NEW_PATCH=$((PATCH + 1))
NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"

echo "New version: $NEW_VERSION"

# Set zip filename with version
ZIP_NAME="ttp-voice-widget-${NEW_VERSION}.zip"

# Update version in plugin file (two places: header comment and TTP_VERSION constant)
sed -i "s/^ \* Version: $CURRENT_VERSION/ * Version: $NEW_VERSION/" "$PLUGIN_FILE"
sed -i "s/define('TTP_VERSION', '$CURRENT_VERSION');/define('TTP_VERSION', '$NEW_VERSION');/" "$PLUGIN_FILE"

echo "Updated version in $PLUGIN_FILE"

# Remove old zip from Downloads if exists
if [ -f "$DEST_DIR/$ZIP_NAME" ]; then
    rm "$DEST_DIR/$ZIP_NAME"
    echo "Removed old zip file from Downloads"
fi

# Create zip file directly in Downloads (exclude .git and existing zip files)
cd "$PLUGIN_DIR"
zip -r "$DEST_DIR/$ZIP_NAME" . -x "*.git*" -x "*.zip" -x "production_deploy.sh" > /dev/null

echo "Created zip file: $DEST_DIR/$ZIP_NAME"

# Show file size
ZIP_SIZE=$(ls -lh "$DEST_DIR/$ZIP_NAME" | awk '{print $5}')
echo "Zip file size: $ZIP_SIZE"

echo ""
echo "✓ Build complete!"
echo "  Version upgraded: $CURRENT_VERSION -> $NEW_VERSION"
echo "  Zip file: $DEST_DIR/$ZIP_NAME"
echo ""
echo "Ready to upload to WordPress!"

