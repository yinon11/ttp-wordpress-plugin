#!/bin/bash

# Script to clear WordPress and PHP caches on remote server
# Usage: ./clear_cache.sh

set -e

REMOTE_HOST="142.4.217.80"
REMOTE_USER="ubuntu"
SSH_KEY="/home/yinon11/.ssh/id_rsa"

echo "=========================================="
echo "Clearing caches on remote server"
echo "=========================================="
echo ""

# Clear PHP OPcache
echo "1. Clearing PHP OPcache..."
ssh -i $SSH_KEY -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} \
  "php -r 'if (function_exists(\"opcache_reset\")) { opcache_reset(); echo \"✓ OPcache cleared\"; } else { echo \"✗ OPcache not enabled\"; }'"

echo ""

# Try to clear WordPress cache via WP-CLI (if available)
echo "2. Attempting to clear WordPress cache..."
ssh -i $SSH_KEY -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} \
  "cd /home/ubuntu/loopitis && wp cache flush 2>/dev/null && echo '✓ WordPress cache cleared' || echo '✗ WP-CLI not available or no cache plugin'"

echo ""
echo "✅ Cache clearing complete!"
echo ""
echo "Next steps:"
echo "1. Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)"
echo "2. Clear browser cache if needed"
echo "3. Check browser console for any JavaScript errors"
