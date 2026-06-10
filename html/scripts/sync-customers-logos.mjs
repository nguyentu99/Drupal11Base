import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const root = path.join(path.dirname(fileURLToPath(import.meta.url)), '..');
const logoDir = path.join(root, 'images', 'LOGO');

function altFromFilename(filename) {
    return filename
        .replace(/\.[^.]+$/i, '')
        .replace(/^logo\s+/i, '')
        .replace(/^z\d+_.+$/i, 'Khách hàng')
        .replace(/_/g, ' ')
        .trim();
}

function buildLogosHtml() {
    const files = fs
        .readdirSync(logoDir)
        .filter((file) => /\.(png|jpe?g|webp|svg)$/i.test(file))
        .sort((a, b) => a.localeCompare(b, 'vi'));

    return files
        .map((file) => {
            const src = `images/LOGO/${encodeURIComponent(file).replace(/%2F/g, '/')}`;
            const alt = altFromFilename(file);

            return `                <a class="customers__logo" href="#"><img src="${src}" width="188" height="72" alt="${alt}"></a>`;
        })
        .join('\n');
}

const logosHtml = buildLogosHtml();
const pattern = /(<div class="customers__logos">)[\s\S]*?(<\/div>)/;

['index.html', 'doi-tac-khach-hang.html'].forEach((file) => {
    const filePath = path.join(root, file);
    let content = fs.readFileSync(filePath, 'utf8');

    if (!pattern.test(content)) {
        console.warn('Skip (no match):', file);
        return;
    }

    content = content.replace(pattern, `$1\n${logosHtml}\n            $2`);
    fs.writeFileSync(filePath, content);
    console.log('Updated', file);
});

console.log('Total logos:', logosHtml.split('customers__logo').length - 1);
