const fs = require('node:fs');
const path = require('node:path');

const moduleRoot = path.resolve(__dirname, '..');
const outputDir = path.join(moduleRoot, 'js/build/translations');
const packages = [
  '@ckeditor/ckeditor5-font',
];

fs.mkdirSync(outputDir, { recursive: true });

const languageFiles = new Set();

for (const packageName of packages) {
  const translationsDir = path.join(
    moduleRoot,
    'node_modules',
    packageName,
    'build',
    'translations',
  );

  if (!fs.existsSync(translationsDir)) {
    continue;
  }

  for (const file of fs.readdirSync(translationsDir)) {
    if (file.endsWith('.js')) {
      languageFiles.add(file);
    }
  }
}

for (const languageFile of languageFiles) {
  const chunks = [];

  for (const packageName of packages) {
    const source = path.join(
      moduleRoot,
      'node_modules',
      packageName,
      'build',
      'translations',
      languageFile,
    );

    if (fs.existsSync(source)) {
      chunks.push(fs.readFileSync(source, 'utf8').trim());
    }
  }

  if (chunks.length > 0) {
    fs.writeFileSync(
      path.join(outputDir, languageFile),
      `${chunks.join('\n')}\n`,
      'utf8',
    );
  }
}
