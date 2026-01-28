#!/bin/bash

# Script to upload all plugin files to remote server via SFTP/rsync
# Usage: ./upload_all.sh [--sync-delete]
#   --sync-delete: Remove files on remote that don't exist locally (default: false)

set -e

# Check for --sync-delete flag
SYNC_DELETE=false
if [ "$1" == "--sync-delete" ] || [ "$1" == "-d" ]; then
  SYNC_DELETE=true
fi

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REMOTE_HOST="142.4.217.80"
REMOTE_USER="ubuntu"
REMOTE_PATH="/home/ubuntu/loopitis/plugins/talktopc"
CONTAINER_NAME="loopitis-wordpress-1"
CONTAINER_PLUGIN_PATH="/var/www/html/wp-content/plugins/talktopc"
SSH_KEY="/home/yinon11/.ssh/id_rsa"

echo "=========================================="
echo "Uploading plugin files to remote server"
if [ "$SYNC_DELETE" = true ]; then
  echo "Mode: SYNC (will delete remote files not in local)"
else
  echo "Mode: UPDATE (will only add/update files, keep old ones)"
fi
echo "=========================================="
echo ""

cd "$PLUGIN_DIR"

# Build rsync command
RSYNC_CMD="rsync -avz --no-perms --no-group --no-owner --no-times -e \"ssh -i $SSH_KEY -o StrictHostKeyChecking=no\""

# Add --delete flag if sync-delete is enabled
if [ "$SYNC_DELETE" = true ]; then
  RSYNC_CMD="$RSYNC_CMD --delete"
fi

# Add excludes
RSYNC_CMD="$RSYNC_CMD \
  --exclude='.git' \
  --exclude='.vscode' \
  --exclude='node_modules' \
  --exclude='.DS_Store' \
  --exclude='*.zip' \
  --exclude='*.log' \
  --exclude='.gitignore' \
  --exclude='.gitattributes' \
  --exclude='*.md' \
  --exclude='production_deploy.sh' \
  --exclude='production_deploy_readme.sh' \
  --exclude='deploy_to_svn.sh' \
  --exclude='clear_cache.sh' \
  --exclude='upload_all.sh' \
  --exclude='.wordpress-org' \
  --exclude='.distignore' \
  --exclude='.attach_pid*' \
  --exclude='*.swp' \
  --exclude='*.swo' \
  --exclude='*~' \
  ./ ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/"

# Execute rsync (ignore exit code 23 which indicates some attributes couldn't be set, but files were transferred)
set +e
eval $RSYNC_CMD
RSYNC_EXIT=$?
set -e

# rsync exit code 23 means some files/attrs couldn't be transferred, but files were still uploaded
# This is common when we can't set permissions/times due to ownership issues
if [ $RSYNC_EXIT -eq 23 ]; then
  echo ""
  echo "⚠️  Warning: Some file attributes couldn't be set (permissions/times), but files were uploaded successfully."
elif [ $RSYNC_EXIT -ne 0 ]; then
  echo ""
  echo "❌ Error: rsync failed with exit code $RSYNC_EXIT"
  exit $RSYNC_EXIT
fi

echo ""
echo "✅ Files uploaded to host: ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}"
echo ""

# Copy files into Docker container
echo "=========================================="
echo "Copying files into Docker container"
echo "Container: ${CONTAINER_NAME}"
echo "=========================================="
echo ""

# Use docker cp to copy files from host to container
# docker cp copies from host filesystem into container
ssh -i $SSH_KEY -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} <<'DOCKER_EOF'
cd /home/ubuntu/loopitis/plugins/talktopc
docker cp . loopitis-wordpress-1:/var/www/html/wp-content/plugins/talktopc/
docker exec loopitis-wordpress-1 chown -R www-data:www-data /var/www/html/wp-content/plugins/talktopc
# Remove development files that shouldn't be in production
docker exec loopitis-wordpress-1 sh -c 'cd /var/www/html/wp-content/plugins/talktopc && rm -f *.sh .attach_pid* 2>/dev/null || true'
DOCKER_EOF

echo ""
echo "✅ Files copied into Docker container!"
echo ""

# Clear PHP OPcache in container
echo "Clearing PHP OPcache in container..."
ssh -i $SSH_KEY -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} \
  "docker exec ${CONTAINER_NAME} php -r 'if (function_exists(\"opcache_reset\")) { opcache_reset(); echo \"✓ OPcache cleared\"; } else { echo \"✗ OPcache not enabled\"; }'"

echo ""
echo "✅ Upload and deployment complete!"
echo "Files are now live in the WordPress container."
echo ""
echo "Next steps:"
echo "1. Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)"
echo "2. Check: https://loopitis.com/wp-admin/admin.php?page=talktopc"
