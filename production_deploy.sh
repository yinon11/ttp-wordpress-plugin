#!/bin/bash

# Script to upgrade plugin version (minor), create zip, and save to Downloads
# Usage: ./production_deploy.sh [--no-version-bump]
#   --no-version-bump: Skip version increment and use current version

set -e

# Check for --no-version-bump flag
NO_VERSION_BUMP=false
if [ "$1" == "--no-version-bump" ] || [ "$1" == "-n" ]; then
    NO_VERSION_BUMP=true
fi

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

# Determine version to use
if [ "$NO_VERSION_BUMP" = true ]; then
    NEW_VERSION="$CURRENT_VERSION"
    echo "Skipping version bump - using current version: $NEW_VERSION"
else
    # Increment minor version (e.g., 1.9.1 -> 1.9.2)
    IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
    MAJOR="${VERSION_PARTS[0]}"
    MINOR="${VERSION_PARTS[1]}"
    PATCH="${VERSION_PARTS[2]}"
    
    # Increment patch version (minor upgrade)
    NEW_PATCH=$((PATCH + 1))
    NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"
    
    echo "New version: $NEW_VERSION"
    
    # Update version in plugin file (two places: header comment and TTP_VERSION constant)
    sed -i "s/^ \* Version: $CURRENT_VERSION/ * Version: $NEW_VERSION/" "$PLUGIN_FILE"
    sed -i "s/define('TTP_VERSION', '$CURRENT_VERSION');/define('TTP_VERSION', '$NEW_VERSION');/" "$PLUGIN_FILE"
    
    # Update version in readme.txt (Stable tag)
    README_FILE="$PLUGIN_DIR/readme.txt"
    if [ -f "$README_FILE" ]; then
        sed -i "s/^Stable tag: $CURRENT_VERSION/Stable tag: $NEW_VERSION/" "$README_FILE"
        echo "Updated version in $README_FILE"
    fi
    
    echo "Updated version in $PLUGIN_FILE"
fi

# Set zip filename and folder name (matching text domain)
ZIP_NAME="talktopc-voice-widget-${NEW_VERSION}.zip"
PLUGIN_FOLDER_NAME="talktopc-voice-widget"

# Remove old zip from Downloads if exists
if [ -f "$DEST_DIR/$ZIP_NAME" ]; then
    rm "$DEST_DIR/$ZIP_NAME"
    echo "Removed old zip file from Downloads"
fi

# Create temporary directory with correct folder name for WordPress.org
TEMP_DIR=$(mktemp -d)
PLUGIN_TEMP_DIR="$TEMP_DIR/$PLUGIN_FOLDER_NAME"
mkdir -p "$PLUGIN_TEMP_DIR"

echo "Creating temporary plugin directory: $PLUGIN_TEMP_DIR"

# Copy all files except .git, zip files, and the deploy script
cd "$PLUGIN_DIR"
rsync -a --exclude='.git' --exclude='*.zip' --exclude='production_deploy.sh' --exclude='.gitignore' --exclude='.gitattributes' . "$PLUGIN_TEMP_DIR/"

# Create zip file from temp directory (ensures correct folder structure)
cd "$TEMP_DIR"
zip -r "$DEST_DIR/$ZIP_NAME" "$PLUGIN_FOLDER_NAME" > /dev/null

# Clean up temporary directory
rm -rf "$TEMP_DIR"

echo "Created zip file: $DEST_DIR/$ZIP_NAME"
echo "  Folder structure: $PLUGIN_FOLDER_NAME/ (correct for WordPress.org)"

# Show file size
ZIP_SIZE=$(ls -lh "$DEST_DIR/$ZIP_NAME" | awk '{print $5}')
echo "Zip file size: $ZIP_SIZE"

echo ""
echo "✓ Build complete!"
if [ "$NO_VERSION_BUMP" = true ]; then
    echo "  Version: $NEW_VERSION (no change)"
else
    echo "  Version upgraded: $CURRENT_VERSION -> $NEW_VERSION"
fi
echo "  Zip file: $DEST_DIR/$ZIP_NAME"
echo ""
echo "Ready to upload to WordPress!"

