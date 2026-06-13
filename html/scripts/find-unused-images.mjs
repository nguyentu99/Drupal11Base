import fs from 'fs';
import path from 'path';

const root = path.resolve('cnc');
const mediaExts = new Set(['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp', '.ico', '.mp4', '.webm']);
const sourceExts = /\.(html|scss|css|js|mjs|json)$/i;

function walkMedia(dir, files = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      if (entry.name === 'node_modules') continue;
      walkMedia(fullPath, files);
    } else if (mediaExts.has(path.extname(entry.name).toLowerCase())) {
      files.push(fullPath);
    }
  }
  return files;
}

function readAllSources(dir, chunks = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      if (entry.name === 'node_modules') continue;
      readAllSources(fullPath, chunks);
    } else if (sourceExts.test(entry.name)) {
      chunks.push(fs.readFileSync(fullPath, 'utf8'));
    }
  }
  return chunks.join('\n');
}

function isReferenced(filePath, content) {
  const relFromCnc = path.relative(root, filePath).split(path.sep).join('/');
  const basename = path.basename(filePath);
  const candidates = new Set([
    relFromCnc,
    `./${relFromCnc}`,
    `images/${relFromCnc.replace(/^images\//, '')}`,
    `./images/${relFromCnc.replace(/^images\//, '')}`,
    basename,
    relFromCnc.replace(/ /g, '%20'),
    encodeURI(relFromCnc),
    encodeURI(`images/${relFromCnc.replace(/^images\//, '')}`),
  ]);

  // Match percent-encoded path segments (e.g. LOGO/ChatGPT%20Image...)
  const encodedSegments = relFromCnc
    .split('/')
    .map((segment) => encodeURIComponent(segment))
    .join('/');
  candidates.add(encodedSegments);
  candidates.add(`images/${encodedSegments.replace(/^images\//, '')}`);

  for (const candidate of candidates) {
    if (candidate && content.includes(candidate)) return true;
  }

  const parts = relFromCnc.split('/');
  for (let i = 0; i < parts.length - 1; i++) {
    const partial = parts.slice(i).join('/');
    if (content.includes(partial)) return true;
    if (content.includes(partial.replace(/ /g, '%20'))) return true;
    if (content.includes(encodeURI(partial))) return true;
  }

  return false;
}

const allFiles = walkMedia(root);
const content = readAllSources(root);
const unused = allFiles.filter((file) => !isReferenced(file, content));

console.log(`Total media: ${allFiles.length}`);
console.log(`Used: ${allFiles.length - unused.length}`);
console.log(`Unused: ${unused.length}`);
console.log('---UNUSED---');
for (const file of unused.sort()) {
  const stat = fs.statSync(file);
  console.log(`${path.relative(root, file).split(path.sep).join('/')} (${Math.round(stat.size / 1024)} KB)`);
}

const totalBytes = unused.reduce((sum, file) => sum + fs.statSync(file).size, 0);
console.log(`---TOTAL SAVINGS: ${Math.round(totalBytes / 1024 / 1024 * 100) / 100} MB---`);

if (process.argv.includes('--delete')) {
  let deleted = 0;
  for (const file of unused) {
    fs.unlinkSync(file);
    deleted++;
  }
  console.log(`Deleted ${deleted} files.`);
}
