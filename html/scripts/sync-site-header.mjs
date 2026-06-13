import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const cncDir = path.resolve(__dirname, '..');

const indexHtml = fs.readFileSync(path.join(cncDir, 'index.html'), 'utf8');
const headerMatch = indexHtml.match(/<header class="site-header">[\s\S]*?<\/header>/);

if (!headerMatch) {
  console.error('Header not found in index.html');
  process.exit(1);
}

const headerTemplate = headerMatch[0];

const SERVICE_PAGES = new Set([
  'dich-vu.html',
  'giai-phap-ha-tang-kcn.html',
  'thiet-ke-xay-dung-cong-trinh-cong-nghiep.html',
  'thiet-lap-he-thong-ky-thuat-noi-that-nha-xuong.html',
  'thiet-ke-thi-cong-giai-phap-phong-chay-cong-nghiep.html',
  'khai-thac-va-xuc-tien-dau-tu-kcn.html',
  'quan-ly-van-hanh-kcn.html',
  'tu-van-phap-ly-dau-tu.html',
  'tien-ich-nha-o-van-phong.html',
]);

const PROJECT_PAGES = new Set([
  'kcn-nam-binh-xuyen-green-park.html',
  'cnctech-ba-thien-1.html',
  'tt-logistics-bac-giang.html',
  'kcn-thang-long-3.html',
  'khu-cong-nghiep-binh-xuyen.html',
  'kcn-thang-long-2.html',
  'cnctech-ha-nam.html',
  'cum-cong-nghiep-hop-thinh.html',
]);

const NEWS_PAGES = new Set(['tin-tuc-doanh-nghiep.html', 'tin-tuc-chi-tiet.html']);

const RESOURCE_PAGES = {
  'cam-nang-dau-tu.html': 'cam-nang-dau-tu.html',
  'moi-truong-dau-tu-viet-nam.html': 'cam-nang-dau-tu.html',
  'nghien-cuu-thi-truong.html': 'nghien-cuu-thi-truong.html',
  'bao-cao-thi-truong-chi-tiet.html': 'nghien-cuu-thi-truong.html',
  'thu-vien-anh.html': 'thu-vien-anh.html',
};

function addClass(tag, className) {
  return tag.replace(/class="([^"]*)"/, (match, classes) => {
    if (classes.split(/\s+/).includes(className)) return match;
    return `class="${classes} ${className}"`;
  });
}

function activateSubmenuLink(header, href) {
  const escaped = href.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const re = new RegExp(
    `<a class="site-header__submenu-link([^"]*)"([^>]*href="${escaped}"[^>]*)>`,
    'g'
  );

  return header.replace(re, (full, extraClasses, attrs) => {
    let classes = `site-header__submenu-link${extraClasses}`.trim();
    if (!/\bactive\b/.test(classes)) classes += ' active';
    let nextAttrs = attrs;
    if (!/aria-current/.test(nextAttrs)) nextAttrs += ' aria-current="page"';
    return `<a class="${classes}"${nextAttrs}>`;
  });
}

function activateDropdownByLabel(header, label) {
  const re = new RegExp(
    `(<a class="nav-link dropdown-toggle)([^"]*)"([^>]*>\\s*${label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`,
    'g'
  );
  return header.replace(re, (match, start, mid) => {
    if (/active/.test(match)) return match;
    return `${addClass(`${start}${mid}"`, 'active')}`;
  });
}

function activateNavParent(header) {
  return header.replace(
    /<a class="nav-link site-header__nav-parent"/,
    '<a class="nav-link site-header__nav-parent active"'
  );
}

function activateNavLink(header, href) {
  const re = new RegExp(
    `(<a class="nav-link")( href="${href.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}")`,
    'g'
  );
  return header.replace(re, '<a class="nav-link active"$2');
}

function applyActiveState(header, file) {
  let out = header;

  if (file === 'index.html') return out;

  if (file === 'mang-luoi-hoat-dong.html') {
    out = activateDropdownByLabel(out, 'Về chúng tôi');
    out = activateSubmenuLink(out, 'mang-luoi-hoat-dong.html');
    return out;
  }

  if (file === 'doi-tac-khach-hang.html') {
    out = activateNavLink(out, 'doi-tac-khach-hang.html');
    return out;
  }

  if (SERVICE_PAGES.has(file)) {
    out = activateNavParent(out);
    out = activateSubmenuLink(out, file);
    return out;
  }

  if (PROJECT_PAGES.has(file)) {
    out = activateDropdownByLabel(out, 'Dự án');
    out = activateSubmenuLink(out, file);
    return out;
  }

  if (NEWS_PAGES.has(file)) {
    out = activateDropdownByLabel(out, 'Tin tức');
    out = activateSubmenuLink(out, 'tin-tuc-doanh-nghiep.html');
    return out;
  }

  if (RESOURCE_PAGES[file]) {
    out = activateDropdownByLabel(out, 'Tài nguyên');
    out = activateSubmenuLink(out, RESOURCE_PAGES[file]);
    return out;
  }

  return out;
}

const files = fs.readdirSync(cncDir).filter((f) => f.endsWith('.html'));

for (const file of files) {
  const filePath = path.join(cncDir, file);
  const html = fs.readFileSync(filePath, 'utf8');

  if (!/<header class="site-header">[\s\S]*?<\/header>/.test(html)) {
    console.log('skip (no header):', file);
    continue;
  }

  const nextHeader = applyActiveState(headerTemplate, file);
  const nextHtml = html.replace(/<header class="site-header">[\s\S]*?<\/header>/, nextHeader);

  if (nextHtml === html) {
    console.log('ok:', file);
    continue;
  }

  fs.writeFileSync(filePath, nextHtml);
  console.log('updated:', file);
}

console.log('done');
