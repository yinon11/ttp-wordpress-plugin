=== TalkToPC Voice Widget ===
Contributors: yinon11
Tags: voice, ai, chatbot, voice assistant, widget
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.9.21
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add AI voice conversations to your WordPress site. Let visitors talk to your AI agent with natural voice interactions.

== Description ==

TalkToPC Voice Widget allows you to add an AI-powered voice assistant to your WordPress website. Visitors can have natural voice conversations with your AI agent.

**Features:**

* Easy setup - just connect your TalkToPC account via OAuth
* Voice-to-voice conversations with AI
* Text chat mode also available
* Works on desktop and mobile
* Customizable AI agent responses
* AI-powered prompt generation from your site content
* Fully customizable widget appearance
* WooCommerce support for product information
* Low latency, natural sounding speech

**How it works:**

1. Sign up at [TalkToPC](https://talktopc.com)
2. Install this plugin and click "Connect to TalkToPC"
3. Your AI agent is auto-created with content from your website!
4. Visitors can now talk to your AI agent

**Use Cases:**

* Customer support automation
* Restaurant reservations
* Appointment booking
* Product information
* FAQ handling
* E-commerce assistance
* And much more!

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ttp-voice-widget` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to TalkToPC in the admin menu to configure the plugin.
4. Click "Connect to TalkToPC" and authorize your account.
5. An AI agent will be automatically created based on your site content.
6. The widget will appear on your site immediately!

== Frequently Asked Questions ==

= How do I connect my account? =

Click "Connect to TalkToPC" in the plugin settings. You'll be redirected to authorize the connection, then sent back automatically.

= Does this work on mobile? =

Yes! The voice widget works on both desktop and mobile browsers that support microphone access.

= Is this free? =

The WordPress plugin is free. TalkToPC offers various pricing plans for the AI voice service. Check [talktopc.com](https://talktopc.com) for pricing details.

= Can I customize the AI responses? =

Yes, you can customize the system prompt, first message, voice, language, and many other settings directly in the WordPress admin.

= Can I customize the widget appearance? =

Yes! The plugin includes extensive customization options for colors, sizes, positions, icons, and more.

== Screenshots ==

1. Settings page - Connect your TalkToPC account
2. Agent selection and AI settings override
3. Widget appearance customization
4. Voice widget on your site

== Privacy Policy ==

This plugin connects to the TalkToPC service (talktopc.com) to provide AI voice conversations.

**Data Collection:**
* Voice audio is transmitted to TalkToPC servers only when a visitor actively uses the voice widget
* No data is collected without explicit user interaction (clicking the voice button)
* Conversation data may be processed for AI response generation

**External Services:**
* This plugin loads JavaScript from cdn.talktopc.com (required for voice functionality)
* Voice processing is handled by speech.talktopc.com
* Backend API at backend.talktopc.com

By using this plugin, site owners agree to TalkToPC's Terms of Service and Privacy Policy at https://talktopc.com

== Plugin Structure (For Developers & AI Agents) ==

This section documents the plugin's file structure to help developers and AI coding assistants understand and modify the codebase efficiently.

= File Structure =

`
ttp-voice-widget/
├── ttp-voice-widget.php          # Main entry point (constants, includes)
├── readme.txt                    # This file
└── includes/
    ├── admin-settings.php        # Settings registration & sanitizers
    ├── admin-page.php            # Admin UI (HTML/JS/CSS for settings)
    ├── oauth.php                 # OAuth callback & disconnect handlers
    ├── ajax-handlers.php         # All AJAX endpoints
    └── frontend-widget.php       # Frontend widget rendering
`

= File Descriptions =

**ttp-voice-widget.php**
Main entry point. Contains plugin header, constants (TTP_API_URL, TTP_CONNECT_URL, TTP_VERSION), includes all modules, uninstall cleanup, and plugin action links.

**includes/admin-settings.php**
All register_setting() calls, custom sanitizers (ttp_sanitize_float), backend sync hooks that push settings to TalkToPC API, and admin menu registration.

**includes/admin-page.php**
The ttp_settings_page() function that renders the entire admin UI. Contains all settings cards (Connection, Agent Selection, Overrides, Appearance), JavaScript for agent/voice loading and interactions, and CSS styles.

**includes/oauth.php**
Handles OAuth callback (receives api_key from TalkToPC after authorization) and disconnect action (clears all settings). Security nonce verification included.

**includes/ajax-handlers.php**
All WordPress AJAX endpoints:
* ttp_fetch_agents - Get user's agents from API
* ttp_fetch_voices - Get available voices from API
* ttp_create_agent - Create new agent (with optional AI prompt)
* ttp_update_agent - Update agent settings
* ttp_generate_prompt - Generate system prompt from site content
* ttp_save_agent_selection - Save selected agent
* ttp_get_signed_url - Get signed URL for widget (public)

**includes/frontend-widget.php**
Enqueues widget script from CDN, builds configuration object from all settings, and injects initialization script on the frontend.

= API Endpoints Used =

* POST /api/developers/api-keys - Create API key (React frontend, during OAuth)
* GET /api/public/wordpress/agents - List user's agents
* POST /api/public/wordpress/agents - Create new agent
* PUT /api/public/wordpress/agents/{id} - Update agent
* GET /api/public/wordpress/voices - List available voices
* POST /api/public/wordpress/generate-prompt - AI prompt generation
* POST /api/public/agents/signed-url - Get signed URL for widget

= Common Modifications =

**Change widget appearance:**
See includes/admin-page.php (settings UI) and includes/frontend-widget.php (config building)

**Add new setting:**
1. Register in includes/admin-settings.php
2. Add UI in includes/admin-page.php
3. Add to config in includes/frontend-widget.php
4. Add to cleanup list in ttp-voice-widget.php

**Modify OAuth flow:**
See includes/oauth.php

**Add new AJAX endpoint:**
See includes/ajax-handlers.php

**Change API URLs:**
See constants in ttp-voice-widget.php

= Working with AI Agents =

When asking an AI assistant to modify this plugin:

1. Share this readme section first to explain the structure
2. Tell the AI which file to look at based on what you want to change
3. The AI can request to see specific files as needed

Example prompts:
* "I want to add a new setting" → AI needs admin-settings.php + admin-page.php
* "Fix OAuth flow" → AI needs includes/oauth.php
* "Change widget appearance" → AI needs frontend-widget.php
* "Add new AJAX endpoint" → AI needs ajax-handlers.php

== Changelog ==

= 1.9.1 =
* Fixed: Removed redundant API key creation during OAuth flow
* Improved: Simplified OAuth callback - key from authorization is used directly
* Improved: Modular file structure for easier maintenance

= 1.9.0 =
* Added: AI-powered prompt generation from site content
* Added: Extensive widget customization options
* Added: WooCommerce product support
* Added: Multiple voice and language options
* Added: Backend sync for agent settings

= 1.0.0 =
* Initial release
* Voice widget integration
* Settings page for configuration

== Upgrade Notice ==

= 1.9.1 =
Bug fix release. Fixes issue with multiple API keys being created during OAuth connection.

= 1.9.0 =
Major update with AI prompt generation, extensive customization, and improved OAuth flow.

= 1.0.0 =
Initial release of TalkToPC Voice Widget.
