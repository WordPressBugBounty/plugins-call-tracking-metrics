#!/bin/bash

PLUGIN_SLUG="call-tracking-metrics"
BUILD_DIR=".build-zip-tmp"
ZIP_NAME="${PLUGIN_SLUG}.zip"

# Ensure we are in the project root
if [ ! -f "composer.json" ]; then
    echo "Error: Could not find composer.json. Are you in the project root?"
    exit 1
fi

echo "🚀 Starting Zip Build for $PLUGIN_SLUG..."

# 1. Clean and Prepare Build Directory
echo "🧹 Cleaning up previous builds..."
rm -rf "$BUILD_DIR"
rm -f "$ZIP_NAME"
mkdir -p "$BUILD_DIR/$PLUGIN_SLUG"

# 2. Copy working files to build directory
echo "📦 Copying working files..."
# Use rsync to copy files while excluding git and other junk
rsync -a --exclude='.git' --exclude='.gitignore' --exclude='.github' \
      --exclude='.vscode' --exclude='.idea' --exclude='node_modules' \
      --exclude='.build-zip-tmp' --exclude='.build-tmp' --exclude='.svn-repo' \
      --exclude='.gemini-clipboard' --exclude='call-tracking-metrics.zip' \
      ./ "$BUILD_DIR/$PLUGIN_SLUG/"

# 3. Install Production Dependencies
echo "📥 Installing Production Dependencies..."
cd "$BUILD_DIR/$PLUGIN_SLUG"
# Install no-dev dependencies, optimize autoloader, quiet output
composer install --no-dev --optimize-autoloader --no-scripts --quiet
if [ $? -ne 0 ]; then
    echo "Error: Composer install failed."
    exit 1
fi

# 4. Remove Development Files from Build
echo "✂️  Removing development files..."
rm -rf ".git"
rm -rf ".gitignore"
rm -rf ".github"
rm -rf ".vscode"
rm -rf ".idea"
rm -rf "tests"
rm -rf "bin"
rm -rf "composer.lock"
rm -rf "composer.json"
rm -rf "package.json"
rm -rf "package-lock.json"
rm -rf "phpunit.xml"
rm -rf "phpunit.xml.dist"
rm -rf "phpstan.neon"
rm -rf "phpstan-simple.neon"
rm -rf "CLAUDE.md"
rm -rf "GEMINI.md"
rm -rf "TECHNOTES.md"
rm -rf "deploy.sh"
rm -rf "build-zip.sh" # Don't include this script itself
rm -rf "README.md" # WordPress uses readme.txt

# Remove junk files
find . -name ".gitkeep" -type f -delete
rm -rf ".phpunit.result.cache"
rm -rf "phpunit-deprecations.xml"
rm -rf "phpunit_debug.log"
rm -rf "junit.xml"
rm -rf "phpunit.xml.dist.bak"

# 5. Create Zip File
echo "🤐 Zipping files..."
cd .. # Go back to .build-zip-tmp root
zip -r -q "../$ZIP_NAME" "$PLUGIN_SLUG"

# 6. Cleanup
cd ..
echo "🧹 Cleaning up temporary build directory..."
rm -rf "$BUILD_DIR"

echo ""
echo "✅ Zip Build Complete!"
echo "📁 Created: $ZIP_NAME"
