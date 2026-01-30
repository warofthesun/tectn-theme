const fs = require('fs');
const path = require('path');

const baseDir = path.join(__dirname, '..', 'node_modules', 'http-proxy', 'lib', 'http-proxy');
const files = ['index.js', 'common.js'];

for (const name of files) {
  const filePath = path.join(baseDir, name);
  if (!fs.existsSync(filePath)) continue;

  const src = fs.readFileSync(filePath, 'utf8');
  const needle = "require('util')._extend";
  if (!src.includes(needle)) continue;

  const out = src.replace(needle, 'Object.assign');
  fs.writeFileSync(filePath, out, 'utf8');
}
