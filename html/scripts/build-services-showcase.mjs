import fs from 'fs';
import path from 'path';

const file = path.resolve('cnc/index.html');
let html = fs.readFileSync(file, 'utf8');

const services = [
  {
    img: 'images/services-bg.jpg',
    title: 'Giải pháp hạ tầng khu<br>công nghiệp',
    desc: 'Cung cấp giải pháp tổng thể từ quy hoạch, san nền, giao thông, cấp điện – cấp nước, thoát nước – xử lý nước thải, viễn thông, cây xanh đến hạ tầng môi trường. Mục tiêu là tạo quỹ “đất sạch” với hạ tầng đồng bộ, sẵn sàng bàn giao cho nhà đầu tư thứ cấp và nhà máy vào hoạt động.',
    href: 'giai-phap-ha-tang-kcn.html',
  },
  {
    img: 'images/service-bg-2.jpg',
    title: 'Thiết kế và xây dựng công trình<br>công nghiệp',
    desc: 'Thực hiện thiết kế cơ sở, thiết kế kỹ thuật – bản vẽ thi công và thi công trọn gói nhà xưởng, kho bãi, công trình phụ trợ và khối văn phòng trong khu công nghiệp. Đảm bảo công trình đạt tiêu chuẩn kỹ thuật, tối ưu chi phí đầu tư, phù hợp dây chuyền sản xuất và quy hoạch tổng thể hạ tầng.',
    href: 'thiet-ke-xay-dung-cong-trinh-cong-nghiep.html',
  },
  {
    img: 'images/service-bg-3.jpg',
    title: 'Thiết lập hệ thống kỹ thuật và nội thất nhà xưởng',
    desc: 'Cung cấp dịch vụ cải tạo, mở rộng, nâng cấp nhà xưởng và thiết lập hệ thống kỹ thuật và nội thất khu văn phòng, khu phụ trợ trong nhà máy theo nhu cầu sản xuất cụ thể của từng khách hàng. Tối ưu công năng, dòng di chuyển, môi trường làm việc (ánh sáng, thông gió, cách nhiệt, tiếng ồn) và giảm chi phí vận hành.',
    href: 'thiet-lap-he-thong-ky-thuat-noi-that-nha-xuong.html',
  },
  {
    img: 'images/service-bg-4.jpg',
    title: 'Thiết kế thi công giải pháp phòng cháy công nghiệp',
    desc: 'Tư vấn giải pháp, thiết kế, thẩm duyệt và thi công các hệ thống Phòng cháy chữa cháy (PCCC) cho nhà xưởng và hạ tầng khu công nghiệp theo quy định pháp luật. Đồng thời cung cấp dịch vụ nghiệm thu, huấn luyện, bảo trì để đảm bảo an toàn cháy nổ trong suốt quá trình vận hành.',
    href: 'thiet-ke-thi-cong-giai-phap-phong-chay-cong-nghiep.html',
  },
  {
    img: 'images/service-bg-1-1.jpg',
    title: 'Khai thác và xúc tiến đầu tư<br>khu công nghiệp',
    desc: 'Dịch vụ chiến lược đầu tư khu công nghiệp giúp Chủ đầu tư tối ưu hoạt động xúc tiến đầu tư, từ nghiên cứu thị trường, phát triển khách hàng đến marketing và kết nối nhà đầu tư quốc tế. Mục tiêu là tăng tỷ lệ lấp đầy, thu hút FDI chất lượng cao, tối đa hóa giá trị tài sản và nâng cao năng lực cạnh tranh của khu công nghiệp.',
    href: 'khai-thac-va-xuc-tien-dau-tu-kcn.html',
  },
  {
    img: 'images/service-bg-2-2.jpg',
    title: 'Quản lý, vận hành<br>khu công nghiệp',
    desc: 'Vận hành đồng bộ hệ thống hạ tầng kỹ thuật (điện, nước, xử lý nước thải, PCCC, an ninh, cảnh quan, vệ sinh) và dịch vụ hỗ trợ nhà đầu tư. Hướng đến mô hình quản lý chuyên nghiệp, minh bạch, nâng cao chất lượng dịch vụ và sức hấp dẫn của khu công nghiệp.',
    href: 'quan-ly-van-hanh-kcn.html',
  },
  {
    img: 'images/service-bg-3-3.jpg',
    title: 'Tư vấn pháp lý <br> đầu tư',
    desc: 'Đồng hành pháp lý cho chủ đầu tư hạ tầng và nhà đầu tư trong suốt vòng đời dự án: từ chủ trương đầu tư, đất đai, xây dựng, môi trường, PCCC, giấy chứng nhận đầu tư, đăng ký kinh doanh, vận hành và mọi giấy phép hoạt động... Giúp rút ngắn thời gian thủ tục, giảm rủi ro pháp lý và đảm bảo tuân thủ đầy đủ quy định của pháp luật.',
    href: 'tu-van-phap-ly-dau-tu.html',
  },
  {
    img: 'images/service-bg-4-4.jpg',
    title: 'Tiện ích nhà ở, <br> văn phòng',
    desc: 'Phát triển và vận hành hệ thống nhà ở công nhân, nhà ở chuyên gia, căn hộ dịch vụ và văn phòng cho thuê gắn với khu công nghiệp. Tạo hệ sinh thái dịch vụ – tiện ích đồng bộ (nhà ở, làm việc, thương mại – dịch vụ) nhằm nâng cao chất lượng sống và giữ chân nguồn nhân lực.',
    href: 'tien-ich-nha-o-van-phong.html',
  },
];

const media = services
  .map(
    (s, i) => `                    <div class="services-showcase__media-item${i === 0 ? ' is-active' : ''}" data-index="${i}">
                        <img class="services-showcase__media-img" src="${s.img}" alt="">
                    </div>`
  )
  .join('\n');

const items = services
  .map((s, i) => {
    const num = String(i + 1).padStart(2, '0');
    return `                    <article class="services-showcase__item${i === 0 ? ' is-active' : ''}" data-index="${i}" tabindex="0">
                        <div class="services-showcase__item-thumb">
                            <img src="${s.img}" alt="">
                        </div>
                        <div class="services-showcase__item-first">
                            <h3 class="services-showcase__item-title">${s.title}</h3>
                        </div>
                        <div class="services-showcase__item-panel">
                            <h3 class="services-showcase__item-title">${s.title}</h3>
                            <p class="services-showcase__item-desc">${s.desc}</p>
                            <span class="services-showcase__item-rule"></span>
                            <a class="btn-see-more" href="${s.href}">xem thêm<span class="btn-see-more__icon"><i class="fa-solid fa-arrow-right"></i></span></a>
                        </div>
                    </article>`;
  })
  .join('\n');

const block = `        <div class="services-showcase">
            <div class="services-showcase__content">
                <div class="services-showcase__media">
${media}
                </div>
                <div class="services-showcase__list">
                    <div class="services-showcase__items">
${items}
                    </div>
                </div>
            </div>
            <div class="services-showcase__nav">
                <button type="button" class="services-showcase__nav-btn services-showcase__nav-btn--prev is-disabled"
                    aria-label="Dịch vụ trước">
                    <img width="48" height="48" src="images/svg/services-arrow-left.svg" alt="">
                </button>
                <button type="button" class="services-showcase__nav-btn services-showcase__nav-btn--next"
                    aria-label="Dịch vụ tiếp theo">
                    <img width="48" height="48" src="images/svg/services-arrow-left.svg" alt="">
                </button>
            </div>
        </div>`;

const re = /        <div class="services-showcase">[\s\S]*?        <\/div>\r?\n    <\/div>\r?\n\r?\n    <div class="section">/;
if (!re.test(html)) {
  console.error('Could not find services-showcase block');
  process.exit(1);
}

html = html.replace(re, `${block}\n    </div>\n\n    <div class="section">`);
fs.writeFileSync(file, html);
console.log('Updated services-showcase HTML');
