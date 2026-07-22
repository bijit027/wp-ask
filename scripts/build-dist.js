const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const rootDir = path.resolve(__dirname, '..');
const distDir = path.join(rootDir, 'dist');
const pluginSlug = 'wpask';
const pluginDistDir = path.join(distDir, pluginSlug);
const zipFileName = `${pluginSlug}.zip`;
const zipFilePath = path.join(distDir, zipFileName);

console.log('🚀 Building WPAsk for WordPress.org distribution...');

// Clean dist directory
if (fs.existsSync(distDir)) {
  fs.rmSync(distDir, { recursive: true, force: true });
}
fs.mkdirSync(pluginDistDir, { recursive: true });

// Remove existing zip file
if (fs.existsSync(zipFilePath)) {
  fs.unlinkSync(zipFilePath);
}

// Files/directories to include
const includeFiles = [
  'wpask.php',
  'includes',
  'assets',
  'languages',
  'readme.txt',
  'changelog.txt',
  'LICENSE',
];

// Files/directories to exclude from distribution
const excludePatterns = [
  '.DS_Store',
  '.gitignore',
  'wpask.zip',
  'test-db.php',
  'scripts',
  'BUILD_STATE.md',
];

// Copy files to dist/wpask/
includeFiles.forEach(item => {
  const srcPath = path.join(rootDir, item);
  const destPath = path.join(pluginDistDir, item);

  if (fs.existsSync(srcPath)) {
    if (fs.statSync(srcPath).isDirectory()) {
      copyDirectory(srcPath, destPath);
    } else {
      fs.copyFileSync(srcPath, destPath);
    }
    console.log(`✓ Copied ${item}`);
  } else {
    console.log(`⚠ Skipped ${item} (not found)`);
  }
});

// Build frontend assets
console.log('\n📦 Building frontend assets...');
try {
  execSync('npm run build', { cwd: rootDir, stdio: 'inherit' });
  console.log('✓ Frontend built successfully');
} catch (error) {
  console.error('✗ Frontend build failed:', error.message);
  process.exit(1);
}

// Copy built assets to dist
const assetsSrc = path.join(rootDir, 'assets');
const assetsDest = path.join(distDir, 'assets');
if (fs.existsSync(assetsSrc)) {
  copyDirectory(assetsSrc, assetsDest);
  console.log('✓ Copied built assets');
}

// Create zip file using system zip command
console.log('\n🗜️ Creating zip file...');
try {
  execSync(`cd ${distDir} && zip -r ${pluginSlug}.zip ${pluginSlug}/`, { stdio: 'inherit' });
  console.log(`✅ Zip file created: ${distDir}/${pluginSlug}.zip`);
  console.log(`\n📦 Distribution package ready for WordPress.org submission!`);
} catch (error) {
  console.error('✗ Zip creation failed:', error.message);
  console.log('\n⚠️ Zip creation failed, but dist folder is ready.');
  console.log('You can manually create a zip from the dist folder.');
  console.log(`Run: cd ${distDir} && zip -r ${pluginSlug}.zip ${pluginSlug}/`);
}

function copyDirectory(src, dest) {
  if (!fs.existsSync(dest)) {
    fs.mkdirSync(dest, { recursive: true });
  }

  const entries = fs.readdirSync(src, { withFileTypes: true });

  for (const entry of entries) {
    // Skip hidden files (starting with .)
    if (entry.name.startsWith('.')) {
      continue;
    }

    // Skip excluded patterns
    if (excludePatterns.includes(entry.name)) {
      continue;
    }

    const srcPath = path.join(src, entry.name);
    const destPath = path.join(dest, entry.name);

    if (entry.isDirectory()) {
      copyDirectory(srcPath, destPath);
    } else {
      fs.copyFileSync(srcPath, destPath);
    }
  }
}
