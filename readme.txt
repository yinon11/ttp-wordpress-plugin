=== TalkToPC Voice Widget ===
Contributors: yinon11
Tags: voice assistant, ai chatbot, voice chat, customer support, woocommerce
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.9.72
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add AI voice conversations to your WordPress site. Connect your TalkToPC account and get a fully configured AI assistant in under 2 minutes!

== Description ==

**Try before you install!** [Live Demo](https://talktopc.com/demos/agent_1900570ad)

---

TalkToPC Voice Widget adds an AI-powered voice assistant to your WordPress website. Visitors can have natural voice conversations with your AI agent - no coding required.

**🚀 Quick Setup (Under 2 Minutes):**

1. Install the plugin
2. Click "Connect to TalkToPC" 
3. Done! Your AI assistant is auto-created with content from your website

The plugin automatically scans your pages, posts, and products to create a personalized AI assistant that knows about your business.

**✨ Features:**

* **One-Click Setup** - OAuth connection with automatic AI agent creation
* **AI-Powered Configuration** - System prompt auto-generated from your site content
* **Voice & Text Modes** - Visitors can speak or type
* **Fully Customizable** - Colors, sizes, positions, icons, and more
* **WooCommerce Support** - AI knows your products, prices, and availability
* **Multi-Language** - Support for Hebrew, English, Spanish, and more
* **Mobile Ready** - Works on all devices with microphone access
* **Low Latency** - Natural, responsive conversations

**🎯 Use Cases:**

* Customer support automation
* Product recommendations
* FAQ handling
* Appointment scheduling
* Restaurant reservations
* Lead qualification
* And much more!

**🔒 Security:**

* Secure OAuth 2.0 connection
* No passwords stored
* One-time secrets with automatic expiration
* All data transmitted over HTTPS

== Installation ==

= Automatic Installation =

1. Go to Plugins → Add New in your WordPress admin
2. Search for "TalkToPC Voice Widget"
3. Click Install Now, then Activate
4. Go to TalkToPC in the admin menu
5. Click "Connect to TalkToPC" and authorize
6. Your AI assistant is ready!

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click Install Now
4. Activate the plugin
5. Go to TalkToPC in the admin menu to connect

== Frequently Asked Questions ==

= How do I connect my account? =

Click "Connect to TalkToPC" in the plugin settings. You'll be redirected to TalkToPC to authorize the connection. After authorization, you'll be sent back to WordPress automatically with your AI assistant ready to go.

= Does the AI know about my website? =

Yes! When you first connect, the plugin scans your pages, posts, and products to create a customized AI assistant. You can also regenerate the prompt anytime by clicking "Generate from Site Content" in the settings.

= Does this work on mobile? =

Yes! The voice widget works on both desktop and mobile browsers that support microphone access.

= Is this free? =

The WordPress plugin is free. TalkToPC offers a free trial and various pricing plans for the AI voice service. Visit [talktopc.com/pricing](https://talktopc.com/upgrade) for details.

= Can I customize the AI responses? =

Yes! You can customize:
* System prompt (AI personality and knowledge)
* First message (greeting)
* Voice selection
* Language
* Temperature (creativity level)
* And more

= Can I customize the widget appearance? =

Absolutely! The plugin includes extensive customization:
* Button position, size, and colors
* Panel dimensions and colors
* Header and footer styling
* Message bubble colors
* Voice interface colors
* Animations and tooltips
* Custom CSS support

= What languages are supported? =

The AI supports multiple languages including English, Hebrew, Spanish, French, German, Arabic, Chinese, Japanese, and more. The plugin auto-detects your site's language.

= Does it work with WooCommerce? =

Yes! The plugin automatically includes your product names, prices, descriptions, and availability in the AI's knowledge base.

== Screenshots ==

1. Connect page - One-click OAuth connection to TalkToPC
2. Settings page - Agent selection with auto-generated AI prompt
3. Customization - Extensive appearance options
4. Live widget - Voice assistant on your site

== Privacy Policy ==

This plugin connects to the TalkToPC service (talktopc.com) to provide AI voice conversations.

**Data Collection:**

* Voice audio is transmitted to TalkToPC servers only when a visitor actively uses the voice widget
* No data is collected without explicit user interaction (clicking the voice button)
* Conversation data is processed for AI response generation
* Site content (pages, posts, products) is sent during initial setup for AI prompt generation

**External Services:**

* Widget JavaScript: cdn.talktopc.com
* Voice processing: speech.talktopc.com  
* Backend API: backend.talktopc.com

By using this plugin, site owners agree to TalkToPC's [Terms of Service](https://talktopc.com/terms) and [Privacy Policy](https://talktopc.com/privacy).

== Plugin Structure (For Developers) ==

= File Structure =
```
talktopc/
├── talktopc.php          # Main entry point
├── readme.txt                    # This file
└── includes/
    ├── admin-settings.php        # Settings registration
    ├── admin-page.php            # Admin UI (HTML/JS/CSS)
    ├── oauth.php                 # OAuth flow handlers
    ├── ajax-handlers.php         # AJAX endpoints
    └── frontend-widget.php       # Frontend widget rendering
```

= Key Files =

* **talktopc.php** - Plugin header, constants, includes
* **admin-settings.php** - register_setting() calls, sanitizers, API sync
* **admin-page.php** - Settings UI with all customization options
* **oauth.php** - Secure OAuth with one-time secret verification
* **ajax-handlers.php** - All AJAX endpoints for agents, voices, prompts
* **frontend-widget.php** - Widget script and configuration injection

= API Endpoints =

* `GET /api/public/wordpress/agents` - List agents
* `POST /api/public/wordpress/agents` - Create agent
* `PUT /api/public/wordpress/agents/{id}` - Update agent
* `GET /api/public/wordpress/voices` - List voices
* `POST /api/public/wordpress/generate-prompt` - AI prompt generation
* `POST /api/public/agents/signed-url` - Widget authentication

= Extending the Plugin =

**Add new setting:**
1. Register in `admin-settings.php`
2. Add UI in `admin-page.php`
3. Add to config in `frontend-widget.php`
4. Add to cleanup in `talktopc.php`

**Modify OAuth:** See `oauth.php`
**Add AJAX endpoint:** See `ajax-handlers.php`

== Changelog ==

= 1.9.72 =
* Security: Added capability checks to all AJAX handlers
* Security: Added proper error handling for all remote API requests
* Security: Float values now clamped to valid ranges in all entry points
* Fixed: Renamed all 'ttp' prefixes to 'talktopc' for WordPress.org compliance
* Improved: Added reviewer-facing security comments for nopriv endpoint

= 1.9.30 =
* Maintenance release

= 1.9.28 =
* New: Redesigned OAuth authorization pages with modern dark theme
* New: Animated progress indicator during setup
* New: "Continue in background" option during agent creation
* Fixed: "Close Window" button now works correctly
* Fixed: Duplicate agent creation when returning to WordPress quickly
* Improved: Setup status tracking with transient-based locking
* Improved: WordPress coding standards compliance
* Security: Added proper nonce verification comments for OAuth endpoint
* Security: Wrapped debug logging in WP_DEBUG check

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

== Upgrade Notice ==

= 1.9.30 =
Maintenance release.

= 1.9.28 =
Redesigned OAuth flow with better UX. Fixed duplicate agent bug. Improved WordPress standards compliance.

= 1.9.1 =
Bug fix for multiple API keys being created during OAuth connection.

= 1.9.0 =
Major update with AI prompt generation and extensive customization options.