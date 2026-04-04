# CLAUDE.md - TTP WordPress Plugin

## Project Overview

TalkToPC Voice Widget — a WordPress plugin that adds an AI-powered voice assistant to websites. Visitors can have natural voice conversations with an AI agent in 40+ languages. Features one-click OAuth setup, automatic AI agent creation from site content, voice & text modes, WooCommerce support, and full customization.

## Tech Stack

- **Language:** PHP 7.4+
- **Platform:** WordPress 5.0+
- **No build tool required** — pure PHP/HTML/CSS/JS plugin

## Project Structure

- `/includes/` - Core functionality (admin settings, AJAX handlers, OAuth, frontend widget, migrations)
- `/assets/images/` - Widget assets
- `/admin-pages/` - Admin UI components
- `/admin-scripts/` - Admin JavaScript
- `ttp-voice-widget.php` - Main plugin entry point

## Build & Run

- **Installation:** Deploy to WordPress plugin directory (`/wp-content/plugins/`)
- **Setup:** Activate plugin -> Click "Connect to TalkToPC" (OAuth)
- **Deployment:** `deploy_to_svn.sh`, `production_deploy.sh` for WordPress.org SVN

No build step needed.

## Development Guidelines

- Follow WordPress coding standards for PHP.
- Core logic goes in `/includes/`.
- Admin UI pages go in `/admin-pages/`.
- Version is managed in the main plugin file header.

## Important

**On any change to this repository, update this CLAUDE.md file to reflect the current state of the project.** Keep the project structure, build instructions, and guidelines accurate and up to date.
