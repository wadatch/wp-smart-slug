#!/bin/bash

# WP Smart Slug Build Script
# This script builds the plugin for distribution

set -e

echo "🔨 Building WP Smart Slug..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PLUGIN_NAME="wp-smart-slug"
BUILD_DIR="build"
DIST_DIR="dist"
VERSION=$(grep "Version:" wp-smart-slug.php | sed 's/.*Version: *//' | sed 's/ *\*\/.*//')

echo -e "${YELLOW}Building version: $VERSION${NC}"

# Clean up previous builds
if [ -d "$BUILD_DIR" ]; then
    rm -rf "$BUILD_DIR"
fi

if [ -d "$DIST_DIR" ]; then
    rm -rf "$DIST_DIR"
fi

mkdir -p "$BUILD_DIR/$PLUGIN_NAME"
mkdir -p "$DIST_DIR"

echo "✅ Cleaned up previous builds"

# Install composer dependencies (production only)
if [ -f "composer.json" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-scripts
    echo "✅ Composer dependencies installed"
fi

# Run tests
echo "🧪 Running tests..."
composer install --dev --no-scripts > /dev/null 2>&1
if ! composer test > /dev/null 2>&1; then
    echo -e "${RED}❌ Tests failed! Build aborted.${NC}"
    exit 1
fi
echo "✅ All tests passed"

# Run code standards check
echo "🔍 Checking code standards..."
if ! composer phpcs > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  Code standards issues found. Running auto-fix...${NC}"
    composer phpcbf > /dev/null 2>&1 || true
fi
echo "✅ Code standards checked"

# Reinstall production dependencies
composer install --no-dev --optimize-autoloader --no-scripts > /dev/null 2>&1

# Copy files to build directory
echo "📁 Copying files..."

# Copy main plugin files
cp -r admin "$BUILD_DIR/$PLUGIN_NAME/"
cp -r assets "$BUILD_DIR/$PLUGIN_NAME/"
cp -r includes "$BUILD_DIR/$PLUGIN_NAME/"
cp -r languages "$BUILD_DIR/$PLUGIN_NAME/"
cp -r vendor "$BUILD_DIR/$PLUGIN_NAME/"
cp wp-smart-slug.php "$BUILD_DIR/$PLUGIN_NAME/"
cp README.md "$BUILD_DIR/$PLUGIN_NAME/"
cp LICENSE "$BUILD_DIR/$PLUGIN_NAME/"

echo "✅ Files copied"

# Create ZIP file
cd "$BUILD_DIR"
ZIP_FILE="../$DIST_DIR/$PLUGIN_NAME-$VERSION.zip"
zip -r "$ZIP_FILE" "$PLUGIN_NAME" > /dev/null
cd ..

echo "✅ ZIP file created: $DIST_DIR/$PLUGIN_NAME-$VERSION.zip"

# Generate checksums
cd "$DIST_DIR"
sha256sum "$PLUGIN_NAME-$VERSION.zip" > "$PLUGIN_NAME-$VERSION.zip.sha256"
md5sum "$PLUGIN_NAME-$VERSION.zip" > "$PLUGIN_NAME-$VERSION.zip.md5"
cd ..

echo "✅ Checksums generated"

# Clean up build directory
rm -rf "$BUILD_DIR"

# Reinstall dev dependencies
composer install --dev --no-scripts > /dev/null 2>&1

echo -e "${GREEN}🎉 Build completed successfully!${NC}"
echo ""
echo "📦 Distribution files:"
echo "   - $DIST_DIR/$PLUGIN_NAME-$VERSION.zip"
echo "   - $DIST_DIR/$PLUGIN_NAME-$VERSION.zip.sha256"
echo "   - $DIST_DIR/$PLUGIN_NAME-$VERSION.zip.md5"
echo ""
echo "📊 Archive size: $(du -h "$DIST_DIR/$PLUGIN_NAME-$VERSION.zip" | cut -f1)"
echo ""
echo -e "${GREEN}Ready for distribution! 🚀${NC}"