<?php
/**
 * Admin Helpers
 * 
 * Banner components and utility functions
 */

if (!defined('ABSPATH')) exit;

/**
 * Render feature discovery banner
 */
function talktopc_render_feature_discovery_banner() {
    // Check if banner was dismissed
    if (get_option('talktopc_feature_banner_dismissed', false)) {
        return;
    }
    ?>
    <div id="talktopc-feature-discovery-banner" class="talktopc-discovery-banner">
        <div class="talktopc-discovery-icon">💡</div>
        <div class="talktopc-discovery-content">
            <strong>Did you know?</strong>
            <p>You can enable call recording, export transcripts, customize AI models, and view conversation analytics in the TTP app.</p>
        </div>
        <div class="talktopc-discovery-actions">
            <a href="https://talktopc.com/agents" target="_blank" class="button button-primary">Explore Features →</a>
            <button type="button" class="button talktopc-dismiss-banner">Dismiss</button>
        </div>
    </div>
    <?php
}

/**
 * Render review request card (shows 7 days after successful connection)
 */
function talktopc_render_review_request_card() {
    // Don't show if dismissed or already reviewed
    if (get_option('talktopc_review_dismissed') || get_option('talktopc_review_done')) {
        return;
    }
    
    // Check when user connected
    $connected_time = get_option('talktopc_connected_time');
    if (!$connected_time) {
        return;
    }
    
    // Wait 7 days after connection
    if (time() - $connected_time < 7 * DAY_IN_SECONDS) {
        return;
    }
    
    ?>
    <div id="talktopc-review-request-card" class="talktopc-review-banner">
        <div class="talktopc-review-icon">💬</div>
        <div class="talktopc-review-content">
            <strong>We'd love your feedback!</strong>
            <p>Your review helps other WordPress users discover TalkToPC. Have a question or issue? We're here to help!</p>
        </div>
        <div class="talktopc-review-actions">
            <a href="https://wordpress.org/support/plugin/talktopc/reviews/#new-post" target="_blank" class="button button-primary" id="talktopc-review-yes">⭐ Leave a Review</a>
            <a href="https://wordpress.org/support/plugin/talktopc/" target="_blank" class="button" id="talktopc-review-support">💬 Get Support</a>
            <button type="button" class="button talktopc-review-dismiss-btn" id="talktopc-review-dismiss">Dismiss</button>
        </div>
    </div>
    <?php
}
