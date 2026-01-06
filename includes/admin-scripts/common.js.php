<?php
/**
 * Common Admin Scripts
 * 
 * Shared JavaScript utilities used across admin pages
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue common admin scripts using WordPress enqueue functions
 * 
 * WordPress Plugin Review: Uses wp_add_inline_script() instead of inline <script> tags
 */
function talktopc_enqueue_common_scripts($hook) {
    // Only load on TalkToPC admin pages
    if (strpos($hook, 'talktopc') === false) {
        return;
    }
    
    // Register dummy script handle (required for wp_add_inline_script)
    wp_register_script('talktopc-common', false, ['jquery'], TALKTOPC_VERSION, true);
    wp_enqueue_script('talktopc-common');
    
    // Pass PHP variables to JavaScript
    wp_localize_script('talktopc-common', 'talktopcCommon', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('talktopc_ajax_nonce'),
    ]);
    
    // Add inline script
    $js = <<<'JS'
    (function($) {
        'use strict';
        
        $(document).ready(function() {
            var ajaxNonce = talktopcCommon.nonce;
            
            // Initialize color pickers
            $(".talktopc-color-picker").wpColorPicker({
                change: function(event, ui) {
                    $(this).closest(".color-picker-row").find(".color-preview").css("background-color", ui.color.toString());
                }
            });
            
            // Collapsible sections
            $(".talktopc-collapsible-header, .collapsible-header").on("click", function() {
                $(this).closest(".talktopc-collapsible, .collapsible").toggleClass("open");
            });
            
            // Icon type toggle
            $("#talktopc_icon_type").on("change", function() {
                var type = $(this).val();
                $(".talktopc-icon-custom-row, .talktopc-icon-emoji-row, .talktopc-icon-text-row").hide();
                if (type === "custom") $(".talktopc-icon-custom-row").show();
                else if (type === "emoji") $(".talktopc-icon-emoji-row").show();
                else if (type === "text") $(".talktopc-icon-text-row").show();
            }).trigger("change");
            
            // Feature discovery banner dismiss
            $(".talktopc-dismiss-banner").on("click", function() {
                var $banner = $("#talktopc-feature-discovery-banner");
                $banner.fadeOut(300, function() { $(this).remove(); });
                
                $.post(talktopcCommon.ajaxUrl, {
                    action: "talktopc_dismiss_feature_banner",
                    nonce: ajaxNonce
                });
            });
            
            // Review request handlers
            $("#talktopc-review-yes, #talktopc-review-support").on("click", function() {
                $.post(talktopcCommon.ajaxUrl, { action: "talktopc_review_action", type: "done", nonce: ajaxNonce });
                $("#talktopc-review-request-card").fadeOut(300);
            });
            
            $("#talktopc-review-dismiss").on("click", function() {
                $.post(talktopcCommon.ajaxUrl, { action: "talktopc_review_action", type: "later", nonce: ajaxNonce });
                $("#talktopc-review-request-card").fadeOut(300);
            });
        });
    })(jQuery);
JS;
    
    wp_add_inline_script('talktopc-common', $js);
}
add_action('admin_enqueue_scripts', 'talktopc_enqueue_common_scripts');

/**
 * Render common admin scripts (deprecated - kept for backwards compatibility)
 * 
 * @deprecated Use talktopc_enqueue_common_scripts() instead
 */
function talktopc_render_common_scripts() {
    // This function is deprecated but kept for backwards compatibility
    // Scripts are now enqueued via admin_enqueue_scripts hook
}

