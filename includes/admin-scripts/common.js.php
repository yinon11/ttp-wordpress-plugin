<?php
/**
 * Common Admin Scripts
 * 
 * Shared JavaScript utilities used across admin pages
 */

if (!defined('ABSPATH')) exit;

/**
 * Render common admin scripts (color pickers, collapsibles, etc.)
 */
function talktopc_render_common_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Initialize color pickers
        $('.talktopc-color-picker').wpColorPicker({
            change: function(event, ui) {
                $(this).closest('.color-picker-row').find('.color-preview').css('background-color', ui.color.toString());
            }
        });
        
        // Collapsible sections
        $('.talktopc-collapsible-header, .collapsible-header').on('click', function() {
            $(this).closest('.talktopc-collapsible, .collapsible').toggleClass('open');
        });
        
        // Icon type toggle
        $('#talktopc_icon_type').on('change', function() {
            var type = $(this).val();
            $('.talktopc-icon-custom-row, .talktopc-icon-emoji-row, .talktopc-icon-text-row').hide();
            if (type === 'custom') $('.talktopc-icon-custom-row').show();
            else if (type === 'emoji') $('.talktopc-icon-emoji-row').show();
            else if (type === 'text') $('.talktopc-icon-text-row').show();
        }).trigger('change');
        
        // Feature discovery banner dismiss
        $('.talktopc-dismiss-banner').on('click', function() {
            var $banner = $('#talktopc-feature-discovery-banner');
            $banner.fadeOut(300, function() { $(this).remove(); });
            
            var ajaxNonce = '<?php echo esc_js(wp_create_nonce('talktopc_ajax_nonce')); ?>';
            $.post(ajaxurl, {
                action: 'talktopc_dismiss_feature_banner',
                nonce: ajaxNonce
            });
        });
        
        // Review request handlers
        $('#talktopc-review-yes, #talktopc-review-support').on('click', function() {
            var ajaxNonce = '<?php echo esc_js(wp_create_nonce('talktopc_ajax_nonce')); ?>';
            $.post(ajaxurl, { action: 'talktopc_review_action', type: 'done', nonce: ajaxNonce });
            $('#talktopc-review-request-card').fadeOut(300);
        });
        
        $('#talktopc-review-dismiss').on('click', function() {
            var ajaxNonce = '<?php echo esc_js(wp_create_nonce('talktopc_ajax_nonce')); ?>';
            $.post(ajaxurl, { action: 'talktopc_review_action', type: 'later', nonce: ajaxNonce });
            $('#talktopc-review-request-card').fadeOut(300);
        });
    });
    </script>
    <?php
}

