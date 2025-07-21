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

## Build and Development Commands

### Initial Setup
```bash
# Install Composer dependencies (when composer.json is created)
composer install

# Install npm dependencies (when package.json is created)
npm install
```

### Build Commands
```bash
# Build plugin zip file for distribution
zip -r wp-smart-slug.zip . -x "*.git*" -x "node_modules/*" -x "tests/*" -x "*.lock" -x "composer.json" -x "package*.json" -x "phpunit.xml" -x ".*" -x "*.md"

# Run PHP CodeSniffer for WordPress standards
vendor/bin/phpcs --standard=WordPress .

# Fix PHP coding standards automatically
vendor/bin/phpcbf --standard=WordPress .

# Run PHPUnit tests
vendor/bin/phpunit

# Generate POT file for translations
wp i18n make-pot . languages/wp-smart-slug.pot
```

### Development Workflow

#### Issue-based Development Flow
1. **Create a new branch for each issue**
   ```bash
   git checkout -b issue-{number}-{short-description}
   # Example: git checkout -b issue-4-translation-abstraction
   ```

2. **Implement the feature/fix**
   - Make changes to PHP files
   - Run `vendor/bin/phpcs` to check coding standards
   - Run `vendor/bin/phpunit` to ensure tests pass

3. **Commit changes**
   ```bash
   git add .
   git commit -m "Implement {feature description} (#{issue-number})"
   ```

4. **Push branch and create PR**
   ```bash
   git push origin issue-{number}-{short-description}
   gh pr create --title "Fix #{issue-number}: {description}" --body "Closes #{issue-number}"
   ```

5. **After PR is merged**
   ```bash
   git checkout main
   git pull origin main
   git branch -d issue-{number}-{short-description}
   ```

6. **The issue will be automatically closed when PR is merged** (due to "Closes #X" in PR body)

#### Build and Release
- Build distribution zip with: `zip -r wp-smart-slug.zip . -x "*.git*" -x "node_modules/*" -x "tests/*" -x "*.lock" -x "composer.json" -x "package*.json" -x "phpunit.xml" -x ".*" -x "*.md"`

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