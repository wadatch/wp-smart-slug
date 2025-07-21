# WP Smart Slug Makefile
# Provides convenient commands for development and building

.PHONY: help install test lint fix build clean dev-setup

# Default target
help:
	@echo "WP Smart Slug - Available Commands:"
	@echo ""
	@echo "  make install     - Install all dependencies"
	@echo "  make dev-setup   - Set up development environment"
	@echo "  make test        - Run all tests"
	@echo "  make lint        - Check code standards"
	@echo "  make fix         - Fix code standards automatically"
	@echo "  make build       - Build plugin for distribution"
	@echo "  make clean       - Clean build artifacts"
	@echo "  make i18n        - Generate translation files"
	@echo ""

# Install dependencies
install:
	@echo "📦 Installing Composer dependencies..."
	composer install
	@echo "✅ Dependencies installed"

# Set up development environment
dev-setup: install
	@echo "🛠️  Setting up development environment..."
	@echo "✅ Development environment ready"

# Run tests
test:
	@echo "🧪 Running tests..."
	composer test
	@echo "✅ Tests completed"

# Check code standards
lint:
	@echo "🔍 Checking code standards..."
	composer phpcs
	@echo "✅ Code standards check completed"

# Fix code standards
fix:
	@echo "🔧 Fixing code standards..."
	composer phpcbf
	@echo "✅ Code standards fixed"

# Build plugin
build:
	@echo "🔨 Building plugin..."
	./build.sh
	@echo "✅ Build completed"

# Clean build artifacts
clean:
	@echo "🧹 Cleaning build artifacts..."
	rm -rf build/
	rm -rf dist/
	@echo "✅ Clean completed"

# Generate translation files
i18n:
	@echo "🌍 Generating translation files..."
	@if command -v wp >/dev/null 2>&1; then \
		wp i18n make-pot . languages/wp-smart-slug.pot --exclude=vendor,node_modules,build,dist; \
		echo "✅ POT file generated"; \
	else \
		echo "❌ WP-CLI not found. Please install WP-CLI to generate translation files."; \
	fi

# Quick development cycle
dev: fix test
	@echo "🚀 Development cycle completed"

# CI/CD pipeline simulation
ci: install test lint
	@echo "✅ CI pipeline completed successfully"