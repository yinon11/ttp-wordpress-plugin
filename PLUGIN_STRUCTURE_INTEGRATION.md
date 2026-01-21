# WordPress Plugin Structure & Integration Guide

## Overview

This document explains the WordPress plugin architecture and how to integrate new features:
- DOM context extraction
- Browser tools (highlight, scroll, navigate, fill, click)
- Visual assistant toggle in agent config
- Sitemap parsing for website knowledge

---

## 1. Settings Page Structure

### Admin Menu Hierarchy

**Location:** `includes/admin-settings.php` (lines 17-24)

```php
add_menu_page('TalkToPC Voice Widget', 'TalkToPC', 'manage_options', 'talktopc', ...);
├── Dashboard (talktopc)
├── Page Rules (talktopc-page-rules)
├── Appearance (talktopc-appearance)
├── Chat (talktopc-chat)
└── Advanced (talktopc-advanced)
```

### Page Rendering Flow

1. **Entry Point:** `includes/admin-page.php`
   - Routes to page-specific renderers
   - Enqueues admin styles/scripts

2. **Page Renderers:** `includes/admin-pages/*.php`
   - `dashboard.php` - Main dashboard with agent selection
   - `page-rules.php` - Per-page agent rules
   - `appearance.php` - UI customization
   - `chat.php` - Chat behavior settings
   - `advanced.php` - Advanced features

3. **Settings Sections:** `includes/admin-settings-sections.php`
   - Reusable form sections (render functions)
   - Used across multiple pages

### Settings Registration

**Location:** `includes/admin-settings.php` (lines 29-218)

Settings are registered using WordPress `register_setting()`:

```php
register_setting('talktopc_settings', 'talktopc_agent_id', [
    'sanitize_callback' => 'sanitize_text_field'
]);
```

**Settings Groups:**
- `talktopc_connection` - OAuth credentials (API key, App ID, email)
- `talktopc_settings` - All widget/agent settings

---

## 2. Agent Configuration Storage

### Storage Mechanism

**WordPress Options API** - All settings stored as WordPress options:

```php
// Core agent selection
get_option('talktopc_agent_id')      // Agent UUID
get_option('talktopc_agent_name')   // Agent display name

// Agent overrides (optional)
get_option('talktopc_override_prompt')
get_option('talktopc_override_first_message')
get_option('talktopc_override_voice')
// ... etc
```

### Configuration Flow

1. **Admin UI** → User edits settings
2. **WordPress Options** → Settings saved via `update_option()`
3. **Backend Sync** → Automatic sync to TalkToPC API via hooks

**Location:** `includes/admin-settings.php` (lines 317-382)

```php
// Hooks trigger sync when options change
add_action('update_option_talktopc_override_prompt', 'talktopc_sync_agent_to_backend');
add_action('update_option_talktopc_override_first_message', 'talktopc_sync_agent_to_backend');
// ... etc
```

### Sync Function

**Function:** `talktopc_sync_agent_to_backend()`

- Collects all override values from WordPress options
- Converts to camelCase for backend API
- Sends PUT request to: `PUT /api/public/wordpress/agents/{agent_id}`

**Backend Payload Format:**
```json
{
  "systemPrompt": "...",
  "firstMessage": "...",
  "voiceId": "...",
  "voiceSpeed": 1.0,
  "agentLanguage": "en",
  "temperature": 0.7,
  "maxTokens": 1000,
  "maxCallDuration": 300
}
```

### Frontend Widget Config

**Location:** `includes/frontend-widget.php` (function `talktopc_build_widget_config()`)

Config object passed to widget SDK:

```javascript
{
  appId: "...",
  agentId: "...",
  button: { ... },
  panel: { ... },
  behavior: { ... },
  // ... UI customization only
}
```

**Note:** Agent settings (prompt, voice, etc.) are NOT sent in widget config. They come from the backend when widget requests signed URL.

---

## 3. API Communication with Backend

### API Base URL

**Constant:** `TALKTOPC_API_URL = 'https://backend.talktopc.com'`

### AJAX Endpoints

**Location:** `includes/ajax-handlers.php`

All admin AJAX endpoints:

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `talktopc_fetch_agents` | GET | Get user's agents list |
| `talktopc_fetch_voices` | GET | Get available voices |
| `talktopc_fetch_credits` | GET | Get user credits |
| `talktopc_create_agent` | POST | Create new agent |
| `talktopc_update_agent` | PUT | Update agent settings |
| `talktopc_generate_prompt` | POST | Generate prompt from site content |
| `talktopc_save_agent_selection` | POST | Save selected agent |
| `talktopc_get_signed_url` | POST | Get signed widget URL (public) |

### Backend API Endpoints

**WordPress Plugin → Backend API:**

1. **GET Agents**
   ```
   GET /api/public/wordpress/agents
   Headers: X-API-Key: {api_key}
   ```

2. **Create Agent**
   ```
   POST /api/public/wordpress/agents
   Headers: X-API-Key: {api_key}, Content-Type: application/json
   Body: { name, site_url, site_name, site_content?, ... }
   ```

3. **Update Agent**
   ```
   PUT /api/public/wordpress/agents/{agent_id}
   Headers: X-API-Key: {api_key}, Content-Type: application/json
   Body: { systemPrompt?, firstMessage?, voiceId?, ... }
   ```

4. **Generate Prompt**
   ```
   POST /api/public/wordpress/generate-prompt
   Headers: X-API-Key: {api_key}, Content-Encoding: gzip
   Body: gzipped JSON { site, pages, posts, products, menus }
   ```

5. **Get Signed URL** (Public endpoint)
   ```
   POST /api/public/agents/signed-url
   Headers: Authorization: Bearer {api_key}
   Body: { agentId, appId, allowOverride: false, expirationMs: 3600000 }
   ```

### Authentication

- **Admin endpoints:** Use `X-API-Key` header
- **Public endpoint:** Use `Authorization: Bearer {api_key}` header
- **API Key stored in:** `talktopc_api_key` option (set via OAuth)

---

## 4. Integration Points for New Features

### 4.1 Visual Assistant Toggle in Agent Config

#### Storage

**Add new option:**
```php
// In includes/admin-settings.php, add to register_setting() calls:
register_setting('talktopc_settings', 'talktopc_visual_assistant_enabled', [
    'sanitize_callback' => 'rest_sanitize_boolean',
    'default' => false
]);
```

#### Admin UI

**Location:** `includes/admin-pages/dashboard.php` or `includes/admin-pages/chat.php`

Add checkbox in Agent Settings section:

```php
<div class="form-row">
    <label for="talktopc_visual_assistant_enabled">Visual Assistant</label>
    <div class="field">
        <label>
            <input type="checkbox" 
                   id="talktopc_visual_assistant_enabled" 
                   name="talktopc_visual_assistant_enabled" 
                   value="1" 
                   <?php checked(get_option('talktopc_visual_assistant_enabled'), '1'); ?>>
            Enable visual assistant (DOM context & browser tools)
        </label>
    </div>
</div>
```

#### Backend Sync

**Add sync hook:**
```php
// In includes/admin-settings.php, add to sync hooks:
add_action('update_option_talktopc_visual_assistant_enabled', 'talktopc_sync_agent_to_backend', 10, 0);
```

**Update sync function:**
```php
// In talktopc_sync_agent_to_backend(), add:
$visual_assistant = get_option('talktopc_visual_assistant_enabled');
if ($visual_assistant === '1') {
    $update_data['visualAssistantEnabled'] = true;
}
```

#### Frontend Widget Config

**Location:** `includes/frontend-widget.php` → `talktopc_build_widget_config()`

```php
// Add to config object:
if (get_option('talktopc_visual_assistant_enabled') === '1') {
    $config['visualAssistant'] = [
        'enabled' => true,
        'domContext' => true,  // Enable DOM extraction
        'tools' => ['highlight', 'scroll', 'navigate', 'fill', 'click']
    ];
}
```

---

### 4.2 DOM Context Extraction

#### Frontend Implementation

**Location:** `includes/frontend-widget.php`

Add DOM extraction function and pass to widget:

```php
function talktopc_extract_dom_context() {
    // This will be executed in browser context via inline script
    return [
        'url' => window.location.href,
        'title' => document.title,
        'elements' => [
            // Extract key interactive elements
            'buttons' => Array.from(document.querySelectorAll('button, a.button, input[type="submit"]'))
                .slice(0, 20)
                .map(el => ({
                    'text': el.textContent.trim(),
                    'type': el.tagName.toLowerCase(),
                    'id': el.id || null,
                    'class': el.className || null,
                    'href': el.href || null
                })),
            'forms' => Array.from(document.querySelectorAll('form'))
                .slice(0, 10)
                .map(form => ({
                    'action': form.action || null,
                    'method': form.method || 'get',
                    'inputs': Array.from(form.querySelectorAll('input, textarea, select'))
                        .map(input => ({
                            'name': input.name || null,
                            'type': input.type || 'text',
                            'placeholder': input.placeholder || null,
                            'required': input.required || false
                        }))
                })),
            'links' => Array.from(document.querySelectorAll('a[href]'))
                .slice(0, 30)
                .map(a => ({
                    'text': a.textContent.trim(),
                    'href': a.href
                })),
            'headings' => Array.from(document.querySelectorAll('h1, h2, h3'))
                .slice(0, 15)
                .map(h => ({
                    'level': h.tagName.toLowerCase(),
                    'text': h.textContent.trim()
                }))
        ],
        'meta' => {
            'viewport': document.querySelector('meta[name="viewport"]')?.content || null,
            'description': document.querySelector('meta[name="description"]')?.content || null
        }
    ];
}
```

**Pass to widget config:**
```php
// In talktopc_build_widget_config(), add:
if (get_option('talktopc_visual_assistant_enabled') === '1') {
    $config['domContext'] = [
        'enabled' => true,
        'extractOnLoad' => true,
        'extractOnChange' => true  // Re-extract on DOM mutations
    ];
    
    // Add inline script to extract DOM context
    $dom_extraction_script = "
    window.talktopcDOMContext = " . wp_json_encode([
        'extract' => 'talktopc_extract_dom_context',
        'autoExtract' => true
    ]) . ";
    ";
    wp_add_inline_script('talktopc-agent-widget', $dom_extraction_script);
}
```

#### Backend Integration

The widget SDK should send DOM context to backend when:
1. Widget initializes (if `extractOnLoad: true`)
2. User interacts with page (if `extractOnChange: true`)
3. Agent requests context (via tool call)

**Backend API:** The agent backend should accept DOM context in tool calls or conversation context.

---

### 4.3 Browser Tools (highlight, scroll, navigate, fill, click)

#### Tool Registration

**Location:** `includes/frontend-widget.php` → `talktopc_build_widget_config()`

Add tools configuration:

```php
if (get_option('talktopc_visual_assistant_enabled') === '1') {
    $config['tools'] = [
        'highlight' => [
            'enabled' => true,
            'selector' => true,  // Allow CSS selectors
            'id' => true,        // Allow element IDs
            'class' => true      // Allow class names
        ],
        'scroll' => [
            'enabled' => true,
            'toElement' => true,
            'toPosition' => true,
            'smooth' => true
        ],
        'navigate' => [
            'enabled' => true,
            'url' => true,
            'relative' => true   // Allow relative URLs
        ],
        'fill' => [
            'enabled' => true,
            'selector' => true,
            'value' => true,
            'submit' => false    // Don't auto-submit forms
        ],
        'click' => [
            'enabled' => true,
            'selector' => true,
            'waitForNavigation' => true
        ]
    ];
}
```

#### Tool Implementation (Widget SDK Side)

The widget SDK should implement these tools:

**1. Highlight Tool**
```javascript
{
  name: 'highlight',
  description: 'Highlight an element on the page',
  parameters: {
    selector: { type: 'string', required: true },
    duration: { type: 'number', default: 2000 }
  },
  execute: async ({ selector, duration }) => {
    const element = document.querySelector(selector);
    if (!element) throw new Error('Element not found');
    
    // Add highlight class
    element.classList.add('talktopc-highlight');
    
    // Scroll into view
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Remove highlight after duration
    setTimeout(() => {
      element.classList.remove('talktopc-highlight');
    }, duration);
    
    return { success: true, element: element.textContent.trim() };
  }
}
```

**2. Scroll Tool**
```javascript
{
  name: 'scroll',
  description: 'Scroll to an element or position',
  parameters: {
    selector: { type: 'string' },
    x: { type: 'number' },
    y: { type: 'number' }
  },
  execute: async ({ selector, x, y }) => {
    if (selector) {
      const element = document.querySelector(selector);
      if (!element) throw new Error('Element not found');
      element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else if (x !== undefined || y !== undefined) {
      window.scrollTo({ 
        left: x || window.scrollX, 
        top: y || window.scrollY, 
        behavior: 'smooth' 
      });
    }
    return { success: true };
  }
}
```

**3. Navigate Tool**
```javascript
{
  name: 'navigate',
  description: 'Navigate to a URL',
  parameters: {
    url: { type: 'string', required: true }
  },
  execute: async ({ url }) => {
    window.location.href = url;
    return { success: true, url: window.location.href };
  }
}
```

**4. Fill Tool**
```javascript
{
  name: 'fill',
  description: 'Fill a form field',
  parameters: {
    selector: { type: 'string', required: true },
    value: { type: 'string', required: true },
    submit: { type: 'boolean', default: false }
  },
  execute: async ({ selector, value, submit }) => {
    const element = document.querySelector(selector);
    if (!element) throw new Error('Element not found');
    if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(element.tagName)) {
      throw new Error('Element is not a form field');
    }
    
    element.value = value;
    element.dispatchEvent(new Event('input', { bubbles: true }));
    element.dispatchEvent(new Event('change', { bubbles: true }));
    
    if (submit) {
      const form = element.closest('form');
      if (form) {
        form.submit();
      }
    }
    
    return { success: true, value: element.value };
  }
}
```

**5. Click Tool**
```javascript
{
  name: 'click',
  description: 'Click an element',
  parameters: {
    selector: { type: 'string', required: true },
    waitForNavigation: { type: 'boolean', default: false }
  },
  execute: async ({ selector, waitForNavigation }) => {
    const element = document.querySelector(selector);
    if (!element) throw new Error('Element not found');
    
    if (waitForNavigation) {
      // Wait for navigation to complete
      return new Promise((resolve) => {
        const timeout = setTimeout(() => {
          resolve({ success: true, navigated: false });
        }, 5000);
        
        window.addEventListener('beforeunload', () => {
          clearTimeout(timeout);
          resolve({ success: true, navigated: true });
        });
        
        element.click();
      });
    } else {
      element.click();
      return { success: true };
    }
  }
}
```

#### CSS for Highlight

**Add to widget stylesheet:**
```css
.talktopc-highlight {
  outline: 3px solid #7C3AED !important;
  outline-offset: 2px !important;
  background-color: rgba(124, 58, 237, 0.1) !important;
  transition: all 0.3s ease !important;
  animation: talktopc-pulse 1s ease-in-out;
}

@keyframes talktopc-pulse {
  0%, 100% { outline-color: #7C3AED; }
  50% { outline-color: #A78BFA; }
}
```

---

### 4.4 Sitemap Parsing for Website Knowledge

#### Sitemap Collection Function

**Location:** `includes/ajax-handlers.php` (extend `talktopc_collect_site_content()`)

Add sitemap parsing:

```php
function talktopc_collect_site_content() {
    // ... existing code ...
    
    // Add sitemap parsing
    $site_content['sitemap'] = talktopc_parse_sitemap();
    
    return $site_content;
}

/**
 * Parse WordPress sitemap for website structure knowledge
 */
function talktopc_parse_sitemap() {
    $sitemap_data = [
        'urls' => [],
        'structure' => []
    ];
    
    // Try WordPress core sitemap (WP 5.5+)
    $sitemap_url = home_url('/wp-sitemap.xml');
    
    // Or use Yoast SEO sitemap if available
    if (defined('WPSEO_VERSION')) {
        $sitemap_url = home_url('/sitemap_index.xml');
    }
    
    // Fetch sitemap
    $response = wp_remote_get($sitemap_url, ['timeout' => 10]);
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        // Fallback: Generate sitemap from WordPress structure
        return talktopc_generate_sitemap_from_wp();
    }
    
    $sitemap_xml = wp_remote_retrieve_body($response);
    
    // Parse XML sitemap
    $xml = simplexml_load_string($sitemap_xml);
    
    if ($xml === false) {
        return talktopc_generate_sitemap_from_wp();
    }
    
    // Extract URLs
    foreach ($xml->url as $url) {
        $sitemap_data['urls'][] = [
            'loc' => (string) $url->loc,
            'lastmod' => isset($url->lastmod) ? (string) $url->lastmod : null,
            'changefreq' => isset($url->changefreq) ? (string) $url->changefreq : null,
            'priority' => isset($url->priority) ? (float) $url->priority : null
        ];
    }
    
    // Group by path structure
    $sitemap_data['structure'] = talktopc_organize_sitemap_structure($sitemap_data['urls']);
    
    return $sitemap_data;
}

/**
 * Generate sitemap from WordPress structure (fallback)
 */
function talktopc_generate_sitemap_from_wp() {
    $urls = [];
    
    // Homepage
    $urls[] = [
        'loc' => home_url('/'),
        'priority' => 1.0,
        'changefreq' => 'daily'
    ];
    
    // Pages
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'numberposts' => -1
    ]);
    
    foreach ($pages as $page) {
        $urls[] = [
            'loc' => get_permalink($page->ID),
            'lastmod' => get_post_modified_time('c', false, $page->ID),
            'priority' => 0.8,
            'changefreq' => 'monthly'
        ];
    }
    
    // Posts
    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 100
    ]);
    
    foreach ($posts as $post) {
        $urls[] = [
            'loc' => get_permalink($post->ID),
            'lastmod' => get_post_modified_time('c', false, $post->ID),
            'priority' => 0.6,
            'changefreq' => 'weekly'
        ];
    }
    
    // Categories
    $categories = get_categories(['hide_empty' => false]);
    foreach ($categories as $cat) {
        $urls[] = [
            'loc' => get_category_link($cat->term_id),
            'priority' => 0.5,
            'changefreq' => 'weekly'
        ];
    }
    
    return [
        'urls' => $urls,
        'structure' => talktopc_organize_sitemap_structure($urls)
    ];
}

/**
 * Organize sitemap URLs into hierarchical structure
 */
function talktopc_organize_sitemap_structure($urls) {
    $structure = [];
    
    foreach ($urls as $url_data) {
        $url = $url_data['loc'];
        $parsed = parse_url($url);
        $path = isset($parsed['path']) ? trim($parsed['path'], '/') : '';
        
        if (empty($path)) {
            $structure['home'] = $url_data;
            continue;
        }
        
        $parts = explode('/', $path);
        $current = &$structure;
        
        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }
        
        $current['_url'] = $url_data;
    }
    
    return $structure;
}
```

#### Include in Prompt Generation

**Location:** `includes/ajax-handlers.php` → `talktopc_generate_local_prompt()`

Add sitemap context to prompt:

```php
function talktopc_generate_local_prompt($site_content) {
    // ... existing code ...
    
    // Add sitemap context
    if (!empty($site_content['sitemap']['urls'])) {
        $prompt .= "\n=== WEBSITE STRUCTURE (SITEMAP) ===\n";
        $prompt .= "The website has " . count($site_content['sitemap']['urls']) . " pages:\n";
        
        // Group by section
        $sections = [];
        foreach ($site_content['sitemap']['urls'] as $url_data) {
            $url = $url_data['loc'];
            $parsed = parse_url($url);
            $path = isset($parsed['path']) ? trim($parsed['path'], '/') : 'home';
            
            $section = explode('/', $path)[0];
            if (!isset($sections[$section])) {
                $sections[$section] = [];
            }
            $sections[$section][] = $url;
        }
        
        foreach ($sections as $section => $urls) {
            $prompt .= "\n**{$section}** (" . count($urls) . " pages):\n";
            foreach (array_slice($urls, 0, 10) as $url) {
                $prompt .= "- {$url}\n";
            }
            if (count($urls) > 10) {
                $prompt .= "... and " . (count($urls) - 10) . " more\n";
            }
        }
        $prompt .= "\n";
    }
    
    return $prompt;
}
```

#### Backend Integration

When creating/updating agent, sitemap data is included in `site_content`:

```php
// In talktopc_create_agent AJAX handler:
$agent_data['site_content'] = talktopc_collect_site_content();
// This now includes 'sitemap' key with URLs and structure
```

Backend should:
1. Store sitemap data with agent
2. Use for website knowledge in agent context
3. Allow agent to reference URLs from sitemap

---

## 5. File Structure Summary

```
talktopc.php                          # Main plugin file, constants, includes
includes/
├── admin-settings.php                # Settings registration & sync hooks
├── admin-page.php                    # Admin page router
├── admin-settings-sections.php      # Reusable form sections
├── admin-pages/
│   ├── dashboard.php                # Dashboard page (agent selection)
│   ├── page-rules.php               # Page rules management
│   ├── appearance.php               # UI customization
│   ├── chat.php                     # Chat behavior settings
│   └── advanced.php                 # Advanced features
├── admin-scripts/
│   ├── common.js.php                # Shared admin JS
│   ├── dashboard.js.php             # Dashboard-specific JS
│   └── page-rules.js.php            # Page rules JS
├── ajax-handlers.php                # All AJAX endpoints
├── frontend-widget.php               # Widget enqueue & config builder
└── oauth.php                        # OAuth connection handling
```

---

## 6. Integration Checklist

### Visual Assistant Toggle
- [ ] Add `talktopc_visual_assistant_enabled` option registration
- [ ] Add UI checkbox in Agent Settings
- [ ] Add sync hook for backend update
- [ ] Update `talktopc_sync_agent_to_backend()` to include toggle
- [ ] Add to widget config builder

### DOM Context Extraction
- [ ] Create `talktopc_extract_dom_context()` JavaScript function
- [ ] Add DOM extraction config to widget
- [ ] Add inline script for DOM extraction
- [ ] Test extraction on various page types

### Browser Tools
- [ ] Add tools config to widget config builder
- [ ] Implement highlight tool (CSS + JS)
- [ ] Implement scroll tool
- [ ] Implement navigate tool
- [ ] Implement fill tool
- [ ] Implement click tool
- [ ] Add tool CSS styles

### Sitemap Parsing
- [ ] Create `talktopc_parse_sitemap()` function
- [ ] Create `talktopc_generate_sitemap_from_wp()` fallback
- [ ] Create `talktopc_organize_sitemap_structure()` helper
- [ ] Update `talktopc_collect_site_content()` to include sitemap
- [ ] Update prompt generation to include sitemap context
- [ ] Test with WordPress core sitemap and Yoast SEO

---

## 7. Testing Considerations

1. **Settings Persistence:** Verify settings save and sync correctly
2. **Backend Compatibility:** Ensure backend API accepts new fields
3. **Widget SDK:** Verify widget SDK supports new config options
4. **Performance:** DOM extraction should be lightweight
5. **Security:** Sanitize all user inputs and tool parameters
6. **Cross-browser:** Test tools on Chrome, Firefox, Safari
7. **Mobile:** Ensure tools work on mobile devices

---

## 8. Backend API Changes Required

The backend API should support:

1. **Agent Update Endpoint:**
   ```json
   PUT /api/public/wordpress/agents/{agent_id}
   {
     "visualAssistantEnabled": true,
     "systemPrompt": "...",
     ...
   }
   ```

2. **Tool Execution:** Backend should send tool calls to widget SDK

3. **DOM Context:** Backend should accept DOM context in conversation context

4. **Sitemap Data:** Backend should store and use sitemap data for agent knowledge

---

## Notes

- All settings use WordPress Options API for persistence
- Backend sync happens automatically via WordPress hooks
- Widget config is built server-side and passed to client
- Tools are executed client-side by widget SDK
- DOM context extraction happens in browser context
- Sitemap parsing happens server-side during agent creation/prompt generation
