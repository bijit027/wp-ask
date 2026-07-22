const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');
const includesDir = path.join(rootDir, 'includes');

console.log('🔧 Fixing insightpulse/v1 to wpask/v1...');

function fixApiRoutes(dir) {
  const files = fs.readdirSync(dir, { withFileTypes: true });
  
  for (const file of files) {
    const filePath = path.join(dir, file.name);
    
    if (file.isDirectory()) {
      fixApiRoutes(filePath);
    } else if (file.isFile() && file.name.endsWith('.php')) {
      let content = fs.readFileSync(filePath, 'utf8');
      const original = content;
      
      // Replace insightpulse/v1 with wpask/v1
      content = content.replace(/insightpulse\/v1/g, 'wpask/v1');
      
      if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log(`✓ Fixed: ${file.name}`);
      }
    }
  }
}

fixApiRoutes(includesDir);
console.log('\n✅ API route fix complete');
