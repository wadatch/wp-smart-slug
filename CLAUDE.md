# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WP Smart Slug is a WordPress plugin that automatically translates URLs (slugs) to English to prevent long base64-encoded URLs. The plugin intercepts post, page, and media creation to translate titles and filenames into concise English slugs (1-2 words).

## Development Setup

This is a WordPress plugin project in its initial stages. When implementing:

1. **Main Plugin File**: Create `wp-smart-slug.php` with proper WordPress plugin headers
2. **Directory Structure**: Follow WordPress plugin conventions:
   - `/includes/` - Core plugin functionality
   - `/admin/` - Admin interface code
   - `/languages/` - Translation files
   - `/assets/` - CSS, JS, images

## Translation Services

The plugin supports three translation APIs:
- MyMemory Translation API
- LibreTranslate
- DeepL API Free

Implementation should include an abstraction layer to handle all three services uniformly.

## Key WordPress Hooks

When implementing, focus on these WordPress hooks:
- `wp_insert_post` - For translating post/page slugs
- `add_attachment` - For translating media filenames
- `sanitize_file_name` - For cleaning filenames before translation

## Testing Approach

Since this is a WordPress plugin:
- Use PHPUnit with WordPress test suite
- Test against different WordPress versions
- Mock external API calls to translation services
- Test slug generation with various character sets (Japanese, Chinese, etc.)

## Code Standards

Follow WordPress coding standards:
- PHP code should follow WordPress PHP Coding Standards
- Use WordPress APIs for database operations, HTTP requests, and sanitization
- Escape all output, sanitize all input
- Use WordPress nonce for form submissions