/**
 * Copy Vite manifest.json to the correct location for Laravel Vite plugin
 * This ensures Laravel can find the manifest file
 */

const fs = require('fs');
const path = require('path');

const sourceManifest = path.join(__dirname, '../public/build/.vite/manifest.json');
const targetManifest = path.join(__dirname, '../public/build/manifest.json');

try {
    if (fs.existsSync(sourceManifest)) {
        // Copy manifest.json from .vite/ to build/ root
        fs.copyFileSync(sourceManifest, targetManifest);
        console.log('✓ Manifest copied successfully');
    } else {
        console.warn('⚠ Source manifest not found:', sourceManifest);
    }
} catch (error) {
    console.error('✗ Error copying manifest:', error.message);
    process.exit(1);
}
