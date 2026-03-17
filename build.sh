#!/bin/bash

VERSION="1.0.2"
PLUGIN_SLUG="wp-url-manager"
BUILD_DIR="build"
DIST_DIR="dist"

echo "🚀 Building WP URL Manager v${VERSION}..."

rm -rf ${BUILD_DIR}
rm -rf ${DIST_DIR}

mkdir -p ${BUILD_DIR}/${PLUGIN_SLUG}
mkdir -p ${DIST_DIR}

echo "📦 Copying files..."

cp -r admin ${BUILD_DIR}/${PLUGIN_SLUG}/
cp -r includes ${BUILD_DIR}/${PLUGIN_SLUG}/
cp -r languages ${BUILD_DIR}/${PLUGIN_SLUG}/
cp wp-url-manager.php ${BUILD_DIR}/${PLUGIN_SLUG}/
cp uninstall.php ${BUILD_DIR}/${PLUGIN_SLUG}/
cp README.md ${BUILD_DIR}/${PLUGIN_SLUG}/
cp CHANGELOG.md ${BUILD_DIR}/${PLUGIN_SLUG}/

echo "🗑️  Cleaning up..."

find ${BUILD_DIR} -name ".DS_Store" -type f -delete
find ${BUILD_DIR} -name "*.log" -type f -delete
find ${BUILD_DIR} -name "*.tmp" -type f -delete

echo "📦 Creating ZIP archive..."

cd ${BUILD_DIR}
zip -r ../${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip ${PLUGIN_SLUG}
cd ..

echo "✅ Build complete!"
echo "📁 Archive: ${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip"

FILE_SIZE=$(du -h ${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip | cut -f1)
echo "📊 Size: ${FILE_SIZE}"

rm -rf ${BUILD_DIR}

echo "🎉 Done!"
