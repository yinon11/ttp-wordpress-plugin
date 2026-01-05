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
function ttp_render_common_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Initialize color pickers
        $('.ttp-color-picker').wpColorPicker({
            change: function(event, ui) {
                $(this).closest('.color-picker-row').find('.color-preview').css('background-color', ui.color.toString());
            }
        });
        
        // Collapsible sections
        $('.ttp-collapsible-header, .collapsible-header').on('click', function() {
            $(this).closest('.ttp-collapsible, .collapsible').toggleClass('open');
        });
        
        // Icon type toggle
        $('#ttp_icon_type').on('change', function() {
            var type = $(this).val();
            $('.ttp-icon-custom-row, .ttp-icon-emoji-row, .ttp-icon-text-row').hide();
            if (type === 'custom') $('.ttp-icon-custom-row').show();
            else if (type === 'emoji') $('.ttp-icon-emoji-row').show();
            else if (type === 'text') $('.ttp-icon-text-row').show();
        }).trigger('change');
        
        // Feature discovery banner dismiss
        $('.ttp-dismiss-banner').on('click', function() {
            var $banner = $('#ttp-feature-discovery-banner');
            $banner.fadeOut(300, function() { $(this).remove(); });
            
            var ajaxNonce = '<?php echo esc_js(wp_create_nonce('ttp_ajax_nonce')); ?>';
            $.post(ajaxurl, {
                action: 'ttp_dismiss_feature_banner',
                nonce: ajaxNonce
            });
        });
        
        // Review request handlers
        $('#ttp-review-yes, #ttp-review-support').on('click', function() {
            var ajaxNonce = '<?php echo esc_js(wp_create_nonce('ttp_ajax_nonce')); ?>';
            $.post(ajaxurl, { action: 'ttp_review_action', type: 'done', nonce: ajaxNonce });
            $('#ttp-review-request-card').fadeOut(300);
        });
        
        $('#ttp-review-dismiss').on('click', function() {
            var ajaxNonce = '<?php echo esc_js(wp_create_nonce('ttp_ajax_nonce')); ?>';
            $.post(ajaxurl, { action: 'ttp_review_action', type: 'later', nonce: ajaxNonce });
            $('#ttp-review-request-card').fadeOut(300);
        });
    });
    </script>
    <?php
}

