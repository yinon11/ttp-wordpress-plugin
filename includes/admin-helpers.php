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
function ttp_render_feature_discovery_banner() {
    // Check if banner was dismissed
    if (get_option('ttp_feature_banner_dismissed', false)) {
        return;
    }
    ?>
    <div id="ttp-feature-discovery-banner" class="ttp-discovery-banner">
        <div class="ttp-discovery-icon">💡</div>
        <div class="ttp-discovery-content">
            <strong>Did you know?</strong>
            <p>You can enable call recording, export transcripts, customize AI models, and view conversation analytics in the TTP app.</p>
        </div>
        <div class="ttp-discovery-actions">
            <a href="https://talktopc.com/agents" target="_blank" class="button button-primary">Explore Features →</a>
            <button type="button" class="button ttp-dismiss-banner">Dismiss</button>
        </div>
    </div>
    <?php
}

/**
 * Render review request card (shows 7 days after successful connection)
 */
function ttp_render_review_request_card() {
    // Don't show if dismissed or already reviewed
    if (get_option('ttp_review_dismissed') || get_option('ttp_review_done')) {
        return;
    }
    
    // Check when user connected
    $connected_time = get_option('ttp_connected_time');
    if (!$connected_time) {
        return;
    }
    
    // Wait 7 days after connection
    if (time() - $connected_time < 7 * DAY_IN_SECONDS) {
        return;
    }
    
    ?>
    <div id="ttp-review-request-card" class="ttp-review-banner">
        <div class="ttp-review-icon">💬</div>
        <div class="ttp-review-content">
            <strong>We'd love your feedback!</strong>
            <p>Your review helps other WordPress users discover TalkToPC. Have a question or issue? We're here to help!</p>
        </div>
        <div class="ttp-review-actions">
            <a href="https://wordpress.org/support/plugin/talktopc/reviews/#new-post" target="_blank" class="button button-primary" id="ttp-review-yes">⭐ Leave a Review</a>
            <a href="https://wordpress.org/support/plugin/talktopc/" target="_blank" class="button" id="ttp-review-support">💬 Get Support</a>
            <button type="button" class="button ttp-review-dismiss-btn" id="ttp-review-dismiss">Dismiss</button>
        </div>
    </div>
    <?php
}
