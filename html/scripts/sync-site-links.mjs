import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const cncDir = path.resolve(__dirname, '..');

const CNCTECH = 'https://cnctech.com.vn';
const GOOGLE_MAPS =
  'https://www.google.com/maps/place/CNCTech+Th%C4%83ng+Long/@21.3193526,105.658266,17z';

const PROJECT_PAGES = [
  'kcn-nam-binh-xuyen-green-park.html',
  'cnctech-ba-thien-1.html',
  'kcn-thang-long-3.html',
  'khu-cong-nghiep-binh-xuyen.html',
  'tt-logistics-bac-giang.html',
  'cnctech-ha-nam.html',
  'kcn-thang-long-2.html',
  'cum-cong-nghiep-hop-thinh.html',
];

const FOOTER_ABOUT = [
  ['Thông điệp HĐQT', CNCTECH],
  ['Tầm nhìn - Sứ mệnh', CNCTECH],
  ['Lịch sử', CNCTECH],
  ['Văn hóa doanh nghiệp', CNCTECH],
  ['Giải thưởng', CNCTECH],
  ['ESG', CNCTECH],
  ['Đơn vị thành viên', CNCTECH],
  ['Đội ngũ lãnh đạo', CNCTECH],
];

const NETWORK_PROJECTS = [
  ['KCN Thăng Long 3', 'kcn-thang-long-3.html'],
  ['KCN Bá Thiện I', 'cnctech-ba-thien-1.html'],
  ['KCN Bình Xuyên', 'khu-cong-nghiep-binh-xuyen.html'],
  ['Nam Bình Xuyên', 'kcn-nam-binh-xuyen-green-park.html'],
  ['CCN Hợp Thịnh', 'cum-cong-nghiep-hop-thinh.html'],
  ['Trung tâm Logistics Quốc tế', 'tt-logistics-bac-giang.html'],
  ['Khu công nghiệp Thăng Long 2', 'kcn-thang-long-2.html'],
  ['KCN Thăng Long 2', 'kcn-thang-long-2.html'],
  ['Hà Nam IC', 'cnctech-ha-nam.html'],
  ['CNCTech Hà Nam', 'cnctech-ha-nam.html'],
];

function applyGlobalReplacements(html) {
  let out = html;

  for (const [label, url] of FOOTER_ABOUT) {
    const re = new RegExp(
      `<li><a href="#">${label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}<\\/a><\\/li>`,
      'g'
    );
    out = out.replace(
      re,
      `<li><a href="${url}" target="_blank" rel="noopener noreferrer">${label}</a></li>`
    );
  }

  const pairs = [
    [
      /<a class="site-header__submenu-link" href="#">Tin dự án<\/a>/g,
      '<a class="site-header__submenu-link" href="tin-tuc-doanh-nghiep.html">Tin dự án</a>',
    ],
    [
      /<a class="site-header__submenu-link" href="#">Sự kiện<\/a>/g,
      '<a class="site-header__submenu-link" href="tin-tuc-doanh-nghiep.html">Sự kiện</a>',
    ],
    [/<li><a href="#">Tin dự án<\/a><\/li>/g, '<li><a href="tin-tuc-doanh-nghiep.html">Tin dự án</a></li>'],
    [/<li><a href="#">Sự kiện<\/a><\/li>/g, '<li><a href="tin-tuc-doanh-nghiep.html">Sự kiện</a></li>'],
    [/<a class="customers__logo" href="#">/g, '<a class="customers__logo" href="doi-tac-khach-hang.html">'],
    [
      /<a class="contact-strip__map" href="#" target="_blank" rel="noopener noreferrer">/g,
      `<a class="contact-strip__map" href="${GOOGLE_MAPS}" target="_blank" rel="noopener noreferrer">`,
    ],
    [
      /<a class="site-footer__download d-inline-flex mt-3" href="#">/g,
      '<a class="site-footer__download d-inline-flex mt-3" href="lien-he.html">',
    ],
    [
      /<a class="site-footer__privacy" href="#">Chính sách bảo mật<\/a>/g,
      `<a class="site-footer__privacy" href="${CNCTECH}" target="_blank" rel="noopener noreferrer">Chính sách bảo mật</a>`,
    ],
    [
      /<a class="nav-link" href="#happy-cncers">Happy CNCers<\/a>/g,
      '<a class="nav-link" href="doi-tac-khach-hang.html">Happy CNCers</a>',
    ],
    [
      /<a class="nav-link" href="#tuyen-dung">Cơ hội nghề nghiệp<\/a>/g,
      `<a class="nav-link" href="${CNCTECH}" target="_blank" rel="noopener noreferrer">Cơ hội nghề nghiệp</a>`,
    ],
    [
      /<a class="btn-see-more btn-see-more--solid" href="#" target="_blank"/g,
      `<a class="btn-see-more btn-see-more--solid" href="${GOOGLE_MAPS}" target="_blank"`,
    ],
    [
      /<a class="handbook-doc__nav-link" href="#">Loại hình Doanh nghiệp<\/a>/g,
      '<a class="handbook-doc__nav-link" href="cam-nang-dau-tu.html">Loại hình Doanh nghiệp</a>',
    ],
    [
      /<a class="handbook-doc__nav-link" href="#">Đăng ký Doanh nghiệp<\/a>/g,
      '<a class="handbook-doc__nav-link" href="cam-nang-dau-tu.html">Đăng ký Doanh nghiệp</a>',
    ],
    [
      /<a href="#">báo cáo chi tiết tại đây<\/a>/g,
      '<a href="bao-cao-thi-truong-chi-tiet.html">báo cáo chi tiết tại đây</a>',
    ],
    [
      /<h3 class="site-footer__col-title mb-0">Happy CNCers<\/h3>/g,
      '<h3 class="site-footer__col-title mb-0"><a href="doi-tac-khach-hang.html">Happy CNCers</a></h3>',
    ],
    [
      /<h3 class="site-footer__col-title mb-0">Cơ hội nghề nghiệp<\/h3>/g,
      `<h3 class="site-footer__col-title mb-0"><a href="${CNCTECH}" target="_blank" rel="noopener noreferrer">Cơ hội nghề nghiệp</a></h3>`,
    ],
    [
      /<a class="project-specs__download" href="#" download>/g,
      '<a class="project-specs__download" href="lien-he.html">',
    ],
    [
      /<a class="handbook-doc__nav-link handbook-doc__nav-link--download" href="#">/g,
      '<a class="handbook-doc__nav-link handbook-doc__nav-link--download" href="lien-he.html">',
    ],
  ];

  for (const [pattern, replacement] of pairs) {
    out = out.replace(pattern, replacement);
  }

  return out;
}

function linkPagination(html, pageFile) {
  return html.replace(/<a class="page-link" href="#">(\d+)<\/a>/g, (_, n) => {
    const href = n === '1' ? pageFile : `${pageFile}?page=${n}`;
    return `<a class="page-link" href="${href}">${n}</a>`;
  });
}

function linkGalleryCards(html) {
  let i = 0;
  return html.replace(/<article class="gallery-card">[\s\S]*?<\/article>/g, (block) => {
    const page = PROJECT_PAGES[i % PROJECT_PAGES.length];
    i += 1;
    return block
      .replace('<a class="gallery-card__media" href="#">', `<a class="gallery-card__media" href="${page}">`)
      .replace('<h2 class="gallery-card__title"><a href="#">', `<h2 class="gallery-card__title"><a href="${page}">`);
  });
}

function linkBaoCaoCards(html) {
  return html
    .replace(/<a class="news-card__media" href="#">/g, '<a class="news-card__media" href="bao-cao-thi-truong-chi-tiet.html">')
    .replace(
      /<h3 class="news-card__title"><a href="#">/g,
      '<h3 class="news-card__title"><a href="bao-cao-thi-truong-chi-tiet.html">'
    );
}

function linkNetworkProjects(html) {
  let out = html;
  for (const [name, page] of NETWORK_PROJECTS) {
    const re = new RegExp(
      `<p class="network-project__name">${name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}<\\/p>`,
      'g'
    );
    out = out.replace(
      re,
      `<p class="network-project__name"><a href="${page}">${name}</a></p>`
    );
  }
  return out;
}

function patchIndex(html) {
  let out = html;

  if (!out.includes('id="happy-cncers"')) {
    out = out.replace(
      '<div class="section customers">',
      '<div class="section customers" id="happy-cncers">'
    );
  }

  if (!out.includes('partners__title-link')) {
    out = out.replace(
      '<h2 class="t-heading-1 mb-4 mb-lg-5">Mạng lưới đối tác & khách hàng</h2>',
      '<h2 class="t-heading-1 mb-4 mb-lg-5"><a class="text-reset text-decoration-none partners__title-link" href="doi-tac-khach-hang.html">Mạng lưới đối tác & khách hàng</a></h2>'
    );
  }

  if (!out.includes('section--news__more')) {
    out = out.replace(
      '<h2 class="t-heading-1 section--news__title">Tin tức cập nhật</h2>',
      `<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <h2 class="t-heading-1 section--news__title mb-0">Tin tức cập nhật</h2>
            <a class="btn-see-more section--news__more" href="tin-tuc-doanh-nghiep.html">xem tất cả<span class="btn-see-more__icon"><i class="fa-solid fa-arrow-right"></i></span></a>
        </div>`
    );
  }

  if (!out.includes('partners__map-link')) {
    out = out.replace(
      '<img src="images/CNC.Map.png" alt="Bản đồ mạng lưới khách hàng quốc tế của CNC Industrial">',
      '<a class="partners__map-link d-block" href="mang-luoi-hoat-dong.html"><img src="images/CNC.Map.png" alt="Bản đồ mạng lưới khách hàng quốc tế của CNC Industrial"></a>'
    );
  }

  return out;
}

const files = fs.readdirSync(cncDir).filter((f) => f.endsWith('.html'));

for (const file of files) {
  const filePath = path.join(cncDir, file);
  let html = fs.readFileSync(filePath, 'utf8');
  const original = html;

  html = applyGlobalReplacements(html);

  if (file === 'tin-tuc-doanh-nghiep.html') {
    html = linkPagination(html, 'tin-tuc-doanh-nghiep.html');
  }

  if (file === 'nghien-cuu-thi-truong.html') {
    html = linkPagination(html, 'nghien-cuu-thi-truong.html');
  }

  if (file === 'thu-vien-anh.html') {
    html = linkGalleryCards(html);
  }

  if (file === 'bao-cao-thi-truong-chi-tiet.html') {
    html = linkBaoCaoCards(html);
  }

  if (file === 'mang-luoi-hoat-dong.html') {
    html = linkNetworkProjects(html);
  }

  if (file === 'index.html') {
    html = patchIndex(html);
  }

  if (html !== original) {
    fs.writeFileSync(filePath, html);
    console.log('updated:', file);
  }
}

console.log('done');
