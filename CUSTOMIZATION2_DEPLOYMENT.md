# Customization2 Page Deployment Checklist

## Files Created/Modified

### New Files Created:
1. ✅ `includes/admin-pages/customization2.php` - Main page file
2. ✅ `includes/admin-scripts/customization2.js` - JavaScript functionality
3. ✅ `verify_customization2.php` - Verification script (delete after use)

### Files Modified:
1. ✅ `includes/admin-settings.php` - Added menu registration
2. ✅ `includes/admin-page.php` - Added include for customization2.php
3. ✅ `includes/ajax-handlers.php` - Added save handler for Customization2

## Deployment Steps

### Option 1: Manual Upload (if SFTP not working)

1. **Upload these files to your WordPress server:**
   ```
   includes/admin-pages/customization2.php
   includes/admin-scripts/customization2.js
   ```

2. **Update these files on server:**
   ```
   includes/admin-settings.php
   includes/admin-page.php
   includes/ajax-handlers.php
   ```

3. **Clear WordPress cache** (if using caching plugin)

4. **Refresh WordPress admin** - The "Customization2" menu item should appear under TalkToPC

### Option 2: Using Verification Script

1. Upload `verify_customization2.php` to plugin root directory
2. Access: `your-site.com/wp-content/plugins/talktopc/verify_customization2.php`
3. Check all items show ✓
4. Delete `verify_customization2.php` after verification

## Troubleshooting

### Menu Item Not Showing

1. **Clear WordPress cache:**
   - If using WP Super Cache, W3 Total Cache, etc., clear cache
   - Or disable caching temporarily

2. **Check file permissions:**
   ```bash
   chmod 644 includes/admin-pages/customization2.php
   chmod 644 includes/admin-scripts/customization2.js
   ```

3. **Verify includes are loaded:**
   - Check that `includes/admin-page.php` includes customization2.php
   - Check that `includes/admin-settings.php` has the menu registration

4. **Check for PHP errors:**
   - Enable WP_DEBUG in wp-config.php
   - Check error logs

### JavaScript Not Loading

1. **Check browser console** for 404 errors
2. **Verify script path** is correct:
   - Should be: `/wp-content/plugins/talktopc/includes/admin-scripts/customization2.js`
3. **Clear browser cache** (Ctrl+F5 or Cmd+Shift+R)

### Save Not Working

1. **Check AJAX handler is registered:**
   - Look for `talktopc_save_widget_customization2` in ajax-handlers.php
2. **Check nonce verification:**
   - Make sure nonce name matches: `talktopc_customization2_nonce`
3. **Check browser console** for AJAX errors

## Quick Test

After deployment, try accessing:
```
/wp-admin/admin.php?page=talktopc-customization2
```

You should see the Customization2 page with preview and controls.

## File Locations Summary

```
talktopc/
├── includes/
│   ├── admin-pages/
│   │   └── customization2.php          ← NEW
│   ├── admin-scripts/
│   │   └── customization2.js           ← NEW
│   ├── admin-settings.php              ← MODIFIED (added menu)
│   ├── admin-page.php                  ← MODIFIED (added include)
│   └── ajax-handlers.php               ← MODIFIED (added save handler)
└── verify_customization2.php           ← NEW (temporary, delete after use)
```

## Notes

- Settings saved from Customization2 use the same WordPress options as the original Customization page
- Both pages share the same settings, so changes in one affect the other
- The widget config automatically uses these settings when the widget is enabled
