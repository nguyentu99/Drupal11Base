<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileRepositoryInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Imports Dịch vụ (dich_vu) nodes from static HTML content definitions.
 */
class DichVuContentImporter {

  /**
   * Text format used for imported rich text (matches other site content).
   */
  private const TEXT_FORMAT = 'basic_html';

  /**
   * Maps HTML image paths to theme-relative fallbacks when assets are missing.
   */
  private const IMAGE_ALIASES = [
    'search.png' => 'services/process/search.png',
    'San-nen.png' => 'services/process/san-nen.png',
    'san-nen.png' => 'services/process/san-nen.png',
    'Thoat-nuoc.png' => 'services/process/thoat-nuoc.png',
    'thoat-nuoc.png' => 'services/process/thoat-nuoc.png',
    'Canh-quan.png' => 'services/process/canh-quan.png',
    'canh-quan.png' => 'services/process/canh-quan.png',
    'setting.png' => 'services/process/system.png',
    'System.jpg' => 'services/process/system.png',
    'nha-xuong.jpg' => 'services/operations/hero-banner.jpg',
    'thiet-lap.jpg' => 'services/operations/hero-banner.jpg',
    'fdi.jpg' => 'services-page/hub/center.png',
    'tai-sao.jpg' => 'services-page/hub/icon-building.png',
    'van-hanh.jpg' => 'services/operations/hero-banner.jpg',
    'phong-chay.jpg' => 'services/fire-protection/hero-banner.jpg',
    'thiet-ke-phong-chay.jpg' => 'services/fire-protection/hero-banner.jpg',
    'cong-trinh.jpg' => 'services/operations/hero-banner.jpg',
    'services/operations/intro.jpg' => 'services/operations/hero-banner.jpg',
    'services/operations/management1.jpg' => 'services-page/hub/icon-kcn.png',
    'services/fitout/solution-electric.jpg' => 'services-page/hub/icon-infra.png',
    'services/fitout/solution-hvac.jpg' => 'services-page/hub/icon-building.png',
    'p-1.jpg' => 'services/fitout/icon-standard.png',
    'p-2.jpg' => 'services/fitout/icon-package.png',
    'p-3.jpg' => 'services/fitout/icon-safety.png',
    'services/construction/field-infra.jpg' => 'services-page/hub/icon-infra.png',
    'services/construction/field-factory.jpg' => 'services-page/hub/icon-building.png',
    'dich-vu/tk-cong-kiep-banner.jpg' => 'services/operations/hero-banner.jpg',
    'dich-vu/tk-cong-kiep-1.jpg' => 'services/operations/hero-banner.jpg',
    'dich-vu/tk-cong-kiep-2.jpg' => 'services-page/hub/center.png',
    'dich-vu/tk-cong-kiep-3.jpg' => 'services-page/hub/icon-kcn.png',
    'dich-vu/tk-cong-kiep-4.jpg' => 'services-page/hub/icon-building.png',
    'dich-vu/nha-xuong-1.jpg' => 'services/operations/hero-banner.jpg',
    'dich-vu/nha-xuong-2.jpg' => 'services-page/hub/center.png',
    'dich-vu/nha-xuong-3.jpg' => 'services-page/hub/icon-kcn.png',
    'dich-vu/nha-xuong-4.jpg' => 'services-page/hub/icon-building.png',
    'chua-chay-1.jpg' => 'services/fire-protection/hero-banner.jpg',
    'chua-chay-2.jpg' => 'services-page/hub/center.png',
    'chua-chay-3.jpg' => 'services-page/hub/icon-infra.png',
    'chua-chay-4.jpg' => 'services-page/hub/icon-building.png',
    'image 18125.jpg' => 'services/operations/hero-banner.jpg',
    'image 18126.jpg' => 'services-page/hub/center.png',
    'image 18127.jpg' => 'services-page/hub/icon-kcn.png',
    'image 18128.jpg' => 'services-page/hub/icon-building.png',
  ];

  /**
   * Cached file entity IDs keyed by source path.
   *
   * @var array<string, int>
   */
  private array $fileCache = [];

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly FileRepositoryInterface $fileRepository,
  ) {}

  /**
   * Imports all service nodes; skips existing aliases.
   *
   * @return string[]
   *   Status messages.
   */
  public function import(): array {
    $messages = [];
    foreach ($this->getDefinitions() as $definition) {
      $alias = '/' . $definition['alias'];
      if ($this->aliasExists($alias)) {
        $messages[] = (string) t('Skipped @title — alias @alias already exists.', [
          '@title' => $definition['title'],
          '@alias' => $alias,
        ]);
        continue;
      }

      $node = $this->createNode($definition);
      $messages[] = (string) t('Created @title (node @nid) at @alias.', [
        '@title' => $definition['title'],
        '@nid' => $node->id(),
        '@alias' => $alias,
      ]);
    }

    return $messages;
  }

  /**
   * Updates text fields on existing dich_vu nodes to a valid text format.
   *
   * @return string[]
   *   Status messages.
   */
  public function fixTextFormats(): array {
    $messages = [];
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'dich_vu')
      ->accessCheck(FALSE)
      ->execute();

    foreach (Node::loadMultiple($nids) as $node) {
      $changed = $this->fixEntityTextFormats($node);
      if ($changed) {
        $node->save();
        $messages[] = (string) t('Updated text formats on @title (node @nid).', [
          '@title' => $node->label(),
          '@nid' => $node->id(),
        ]);
      }
    }

    if ($messages === []) {
      $messages[] = (string) t('No Dịch vụ nodes required text format updates.');
    }

    return $messages;
  }

  /**
   * Service content mapped from the four HTML detail pages.
   *
   * @return array<int, array<string, mixed>>
   */
  private function getDefinitions(): array {
    return [
      $this->fitoutDefinition(),
      $this->operationsDefinition(),
      $this->fireProtectionDefinition(),
      $this->constructionDefinition(),
    ];
  }

  /**
   * Fitout — thiet-lap-he-thong-ky-thuat-noi-that-nha-xuong.html
   */
  private function fitoutDefinition(): array {
    return [
      'title' => 'Thiết lập hệ thống kỹ thuật và nội thất nhà xưởng',
      'alias' => 'thiet-lap-he-thong-ky-thuat-noi-that-nha-xuong',
      'hero' => 'images/nha-xuong.jpg',
      'intro_image' => 'images/thiet-lap.jpg',
      'body' => '<p>Cung cấp dịch vụ cải tạo, mở rộng, nâng cấp nhà xưởng và thiết lập hệ thống kỹ thuật và nội thất khu văn phòng, khu phụ trợ trong nhà máy theo nhu cầu sản xuất cụ thể của từng khách hàng. Tối ưu công năng, dòng di chuyển, môi trường làm việc (ánh sáng, thông gió, cách nhiệt, tiếng ồn) và giảm chi phí vận hành.</p>',
      'stats' => [
        ['value' => '200', 'unit' => 'tỷ', 'label' => 'Doanh thu 2025'],
        ['value' => '30+', 'unit' => '', 'label' => 'Dự án hoàn thành'],
        ['value' => '8+', 'unit' => '', 'label' => 'Tỉnh thành'],
      ],
      'highlights' => [[
        'image' => 'images/fdi.jpg',
        'title' => 'Đối tác tin cậy của FDI',
        'lead' => '<p>Năm 2025, dịch vụ Fitout của CNC Industrial được hàng chục doanh nghiệp FDI lựa chọn — chủ yếu đến từ Trung Quốc, Đài Loan và Hàn Quốc. Tổng doanh thu đạt <strong>201 tỷ đồng</strong>, tăng trưởng vượt trội so với kế hoạch.</p>',
        'items' => [
          [
            'icon' => 'images/tin-cay-1.svg',
            'title' => 'Giải pháp trọn gói',
            'body' => '<p>Từ khâu thiết kế, thi công, nghiệm thu cấp chứng chỉ hoạt động đến vận hành và bảo trì lâu dài</p>',
          ],
          [
            'icon' => 'images/tin-cay-2.svg',
            'title' => 'Tiêu chuẩn quốc tế',
            'body' => '<p>Thi công đáp ứng audit của các tập đoàn lớn như Apple, Samsung. Hồ sơ chất lượng được số hoá đầy đủ</p>',
          ],
          [
            'icon' => 'images/tin-cay-3.svg',
            'title' => 'Không tai nạn lao động',
            'body' => '<p>Năm 2025, 100% dự án được nghiệm thu bàn giao, không xảy ra tranh chấp, không có tai nạn lao động</p>',
          ],
        ],
      ]],
      'solutions' => [
        [
          'image' => 'images/services/fitout/solution-electric.jpg',
          'title' => 'Hệ thống điện',
          'body' => '<p>Thiết kế và thi công hệ thống điện công nghiệp đầy đủ: trạm biến áp, tủ MSB, UPS, hệ thống chiếu sáng đến cấp điện phân phối. Đạt chuẩn audit Apple, Samsung.</p>',
        ],
        [
          'image' => 'images/services/fitout/solution-hvac.jpg',
          'title' => 'Cơ khí & điều hòa',
          'body' => '<p>Hệ thống HVAC, chiller, AHU, thông gió và làm mát đáp ứng môi trường sản xuất đặc thù với độ ổn định cao.</p>',
        ],
        [
          'image' => 'images/p-1.jpg',
          'title' => 'Phòng sạch (Cleanroom)',
          'body' => '<p>Thiết kế và thi công phòng sạch đạt chuẩn ISO Class 100–100.000. Áp dương/âm, hệ FFU, sàn vinyl, đảm bảo độ sạch tuyệt đối.</p>',
        ],
        [
          'image' => 'images/p-2.jpg',
          'title' => 'ELV, IT & BMS',
          'body' => '<p>Hệ thống điện nhẹ: Smart CCTV, kiểm soát ra vào, hệ thống mạng IT, BMS/PMS giám sát toàn nhà máy và điều khiển tự động.</p>',
        ],
        [
          'image' => 'images/p-3.jpg',
          'title' => 'Nội thất & hoàn thiện',
          'body' => '<p>Thi công nội thất văn phòng, phòng họp, canteen — đồng bộ thiết kế, vật liệu, hoàn thiện công năng theo nhu cầu khách hàng.</p>',
        ],
      ],
      'process' => [
        ['icon' => 'images/search.png', 'title' => 'Khảo sát & tư vấn', 'body' => 'Tiếp nhận yêu cầu, khảo sát hiện trạng, tư vấn giải pháp phù hợp.'],
        ['icon' => 'images/San-nen.png', 'title' => 'Thiết kế & báo giá', 'body' => 'Thiết kế kỹ thuật, bản vẽ thi công, báo giá minh bạch và chi tiết.'],
        ['icon' => 'images/setting.png', 'title' => 'Đặt hàng thiết bị', 'body' => 'Lựa chọn và đặt hàng thiết bị, vật tư theo tiêu chuẩn dự án.'],
        ['icon' => 'images/Thoat-nuoc.png', 'title' => 'Thi công', 'body' => 'Thi công lắp đặt hệ thống kỹ thuật, nội thất theo bản vẽ được duyệt.'],
        ['icon' => 'images/Canh-quan.png', 'title' => 'Nghiệm thu và cấp phép', 'body' => 'Nghiệm thu chất lượng, hoàn thiện hồ sơ cấp phép hoạt động.'],
        ['icon' => 'images/Canh-quan.png', 'title' => 'Vận hành & bảo trì', 'body' => 'Hỗ trợ vận hành, bảo trì định kỳ và nâng cấp hệ thống lâu dài.'],
      ],
      'gallery' => [
        ['title' => 'Hệ thống điện', 'images' => ['images/dich-vu/nha-xuong-1.jpg', 'images/dich-vu/nha-xuong-2.jpg', 'images/dich-vu/nha-xuong-3.jpg', 'images/dich-vu/nha-xuong-4.jpg']],
        ['title' => 'Cơ khí & điều hòa', 'images' => ['images/dich-vu/nha-xuong-1.jpg', 'images/dich-vu/nha-xuong-2.jpg', 'images/dich-vu/nha-xuong-3.jpg', 'images/dich-vu/nha-xuong-4.jpg']],
        ['title' => 'Phòng sạch (Cleanroom)', 'images' => ['images/dich-vu/nha-xuong-1.jpg', 'images/dich-vu/nha-xuong-2.jpg', 'images/dich-vu/nha-xuong-3.jpg', 'images/dich-vu/nha-xuong-4.jpg']],
        ['title' => 'ELV, IT & BMS', 'images' => ['images/dich-vu/nha-xuong-1.jpg', 'images/dich-vu/nha-xuong-2.jpg', 'images/dich-vu/nha-xuong-3.jpg', 'images/dich-vu/nha-xuong-4.jpg']],
        ['title' => 'Nội thất & hoàn thiện', 'images' => ['images/dich-vu/nha-xuong-1.jpg', 'images/dich-vu/nha-xuong-2.jpg', 'images/dich-vu/nha-xuong-3.jpg', 'images/dich-vu/nha-xuong-4.jpg']],
      ],
      'projects' => ['TT Logistics', 'Bá Thiện', 'Green Park'],
      'why_image' => 'images/tai-sao.jpg',
      'why' => [
        ['number' => '01', 'title' => 'Đội ngũ chuyên gia', 'body' => 'Kiến trúc sư và kỹ sư giàu kinh nghiệm trong thiết kế không gian công nghiệp. Đội ngũ thi công lành nghề, am hiểu tiêu chuẩn quốc tế.'],
        ['number' => '02', 'title' => 'Công nghệ tiên tiến', 'body' => 'Áp dụng bê tông lắp ghép tiên tiến, máy móc hiện đại, số hoá hồ sơ 100%. Rút ngắn tiến độ, nâng cao chất lượng công trình.'],
        ['number' => '03', 'title' => 'One-stop service', 'body' => 'Từ tư vấn đầu tư, thiết kế, thi công đến vận hành và bảo trì — không cần phối hợp nhiều nhà thầu, tiết kiệm thời gian và chi phí tối đa.'],
      ],
    ];
  }

  /**
   * Operations — quan-ly-van-hanh-kcn.html
   */
  private function operationsDefinition(): array {
    return [
      'title' => 'Quản lý, vận hành khu công nghiệp',
      'alias' => 'quan-ly-van-hanh-kcn',
      'hero' => 'images/van-hanh.jpg',
      'intro_image' => 'images/services/operations/intro.jpg',
      'body' => '<p>Với sự tăng trưởng nhanh chóng trong việc xây dựng, kinh doanh nhà xưởng công nghiệp của Tập đoàn CNCTech, Khối Quản lý Vận hành cũng có những nỗ lực, cố gắng đáng ghi nhận trong việc quản lý, bảo trì, sửa chữa nhà xưởng được khách hàng thuê hoặc mua, làm hài lòng khách hàng bằng tiêu chí cung cấp dịch vụ xuất sắc.</p><p>Duy trì nhà xưởng luôn ở trong tình trạng tốt, không làm gián đoạn đến hoạt động của khách hàng là nhiệm vụ khó khăn nhưng không kém phần vinh dự đối với từng quản lý và nhân viên của Khối Quản lý Vận hành.</p>',
      'stats' => [
        ['value' => '46', 'unit' => '', 'label' => 'Nhà xưởng đang vận hành'],
        ['value' => '38', 'unit' => '', 'label' => 'Khách hàng đang phục vụ'],
        ['value' => '80', 'unit' => 'ha', 'label' => 'Tổng diện tích nhà xưởng hoạt động'],
      ],
      'highlights' => [[
        'image' => 'images/services/operations/management1.jpg',
        'title' => 'Về khối quản lý vận hành',
        'lead' => '',
        'items' => [
          ['icon' => 'images/services/operations/bullet-icon.svg', 'title' => '', 'body' => '<p>Năm 2025 <strong>không xảy ra sự cố</strong> liên quan đến cháy nổ hoặc hư hại lớn đến tài sản của khách hàng.</p>'],
          ['icon' => 'images/services/operations/bullet-icon.svg', 'title' => '', 'body' => '<p>Một số sự việc nhỏ được Khối xử lý nhanh chóng, đảm bảo quyền lợi chính đáng — <strong>khách hàng không có khiếu nại.</strong></p>'],
          ['icon' => 'images/services/operations/bullet-icon.svg', 'title' => '', 'body' => '<p>Nhà xưởng luôn ở tình trạng tốt, <strong>không gián đoạn hoạt động sản xuất</strong> của khách hàng.</p>'],
          ['icon' => 'images/services/operations/bullet-icon.svg', 'title' => '', 'body' => '<p>Lãnh đạo Khối <strong>chủ động trao đổi</strong> với khách hàng để nắm bắt nhu cầu, mong muốn và hỗ trợ trong phạm vi cho phép.</p>'],
        ],
      ]],
      'solutions' => [],
      'process' => [
        ['icon' => 'images/search.png', 'title' => 'Vận hành trạm xử lý nước thải, trạm điện, PCCC', 'body' => ''],
        ['icon' => 'images/San-nen.png', 'title' => 'Duy tu cây xanh cảnh quan & cấp thoát nước', 'body' => ''],
        ['icon' => 'images/setting.png', 'title' => 'Đảm bảo hoạt động liên tục 24/7', 'body' => ''],
        ['icon' => 'images/Thoat-nuoc.png', 'title' => 'Phòng chống thiên tai, bão lụt, rủi ro', 'body' => ''],
        ['icon' => 'images/Canh-quan.png', 'title' => 'Sửa chữa nhanh: cửa cuốn, cổng điện, thấm dột', 'body' => ''],
      ],
      'gallery' => [[
        'title' => '',
        'images' => ['images/image 18125.jpg', 'images/image 18126.jpg', 'images/image 18127.jpg', 'images/image 18128.jpg'],
      ]],
      'projects' => ['TT Logistics', 'Bá Thiện', 'Green Park'],
      'why' => [],
    ];
  }

  /**
   * Fire protection — thiet-ke-thi-cong-giai-phap-phong-chay-cong-nghiep.html
   */
  private function fireProtectionDefinition(): array {
    return [
      'title' => 'Thiết kế thi công giải pháp phòng cháy công nghiệp',
      'alias' => 'thiet-ke-thi-cong-giai-phap-phong-chay-cong-nghiep',
      'hero' => 'images/phong-chay.jpg',
      'intro_image' => 'images/thiet-ke-phong-chay.jpg',
      'body' => '<p>Tư vấn giải pháp, thiết kế, thẩm duyệt và thi công các hệ thống Phòng cháy chữa cháy (PCCC) cho nhà xưởng và hạ tầng khu công nghiệp theo quy định pháp luật. Đồng thời cung cấp dịch vụ nghiệm thu, huấn luyện, bảo trì để đảm bảo an toàn cháy nổ trong suốt quá trình vận hành.</p>',
      'stats' => [
        ['value' => '200', 'unit' => 'tỷ', 'label' => 'Doanh thu 2025'],
        ['value' => '30+', 'unit' => '', 'label' => 'Dự án hoàn thành'],
        ['value' => '8+', 'unit' => '', 'label' => 'Tỉnh thành'],
      ],
      'highlights' => [],
      'solutions' => [],
      'process' => [
        ['icon' => 'images/search.png', 'title' => 'Tư vấn giải pháp PCCC', 'body' => ''],
        ['icon' => 'images/San-nen.png', 'title' => 'Thiết kế và thẩm duyệt', 'body' => ''],
        ['icon' => 'images/System.jpg', 'title' => 'Thi công hệ thống PCCC', 'body' => ''],
        ['icon' => 'images/Thoat-nuoc.png', 'title' => 'Nghiệm thu & đào tạo vận hành', 'body' => ''],
      ],
      'gallery' => [[
        'title' => '',
        'images' => ['images/chua-chay-1.jpg', 'images/chua-chay-2.jpg', 'images/chua-chay-3.jpg', 'images/chua-chay-4.jpg'],
      ]],
      'projects' => ['TT Logistics', 'Bá Thiện', 'Green Park'],
      'why' => [],
    ];
  }

  /**
   * Construction — thiet-ke-xay-dung-cong-trinh-cong-nghiep.html
   */
  private function constructionDefinition(): array {
    return [
      'title' => 'Thiết kế và xây dựng công trình công nghiệp',
      'alias' => 'thiet-ke-xay-dung-cong-trinh-cong-nghiep',
      'hero' => 'images/cong-trinh.jpg',
      'intro_image' => 'images/dich-vu/tk-cong-kiep-banner.jpg',
      'body' => '<p>CNCTech hiện đang vận hành và phát triển hệ sinh thái hạ tầng công nghiệp với hơn 6 dự án hạ tầng lớn tại miền Bắc, tập trung chủ yếu tại Phú Thọ, với hai mảng chính: xây dựng hạ tầng khu công nghiệp và xây dựng nhà xưởng.</p><p>Với giải pháp một điểm chạm — từ xây dựng nhà kho, nhà xưởng cơ bản đến tư vấn giải pháp, cấp phép đầu tư, lắp đặt máy móc và vận hành — khách hàng được đồng hành để đưa nhà máy vào hoạt động một cách nhanh nhất, hiệu quả nhất.</p>',
      'stats' => [
        ['value' => '300+', 'unit' => 'ha', 'label' => 'Quy mô Green Park Nam Bình Xuyên'],
        ['value' => '80+', 'unit' => 'ha', 'label' => 'Quy đất phát triển'],
        ['value' => '18', 'unit' => 'ha', 'label' => 'Nhà xưởng đã bàn giao'],
      ],
      'highlights' => [],
      'solutions' => [
        [
          'image' => 'images/services/construction/field-infra.jpg',
          'title' => 'Hạ tầng khu công nghiệp',
          'body' => '<p>San nền, đường giao thông, cấp thoát nước và hệ thống xử lý nước thải — kiến tạo khu công nghiệp xanh, thông minh và hiện đại bậc nhất miền Bắc.</p>',
        ],
        [
          'image' => 'images/services/construction/field-factory.jpg',
          'title' => 'Xây dựng nhà xưởng',
          'body' => '<p>Nhà xưởng xây sẵn và xây theo yêu cầu với hạ tầng đồng bộ, sẵn sàng bàn giao để khách hàng lắp đặt máy móc và đưa nhà máy vào vận hành.</p>',
        ],
      ],
      'process' => [
        ['icon' => 'images/search.png', 'title' => 'Khảo sát nhu cầu sản xuất', 'body' => ''],
        ['icon' => 'images/San-nen.png', 'title' => 'Thiết kế layout và hệ thống', 'body' => ''],
        ['icon' => 'images/setting.png', 'title' => 'Thi công lắp đặt (điện, HVAC...)', 'body' => ''],
        ['icon' => 'images/Thoat-nuoc.png', 'title' => 'Hoàn thiện nội thất khu văn phòng', 'body' => ''],
      ],
      'gallery' => [
        ['title' => 'Thiết kế 3D', 'images' => ['images/dich-vu/tk-cong-kiep-1.jpg', 'images/dich-vu/tk-cong-kiep-2.jpg', 'images/dich-vu/tk-cong-kiep-3.jpg', 'images/dich-vu/tk-cong-kiep-4.jpg']],
        ['title' => 'Công trình thi công', 'images' => ['images/dich-vu/tk-cong-kiep-1.jpg', 'images/dich-vu/tk-cong-kiep-2.jpg', 'images/dich-vu/tk-cong-kiep-3.jpg', 'images/dich-vu/tk-cong-kiep-4.jpg']],
      ],
      'projects' => ['TT Logistics', 'Bá Thiện', 'Green Park'],
      'why' => [],
    ];
  }

  /**
   * Creates one dich_vu node from a definition array.
   */
  private function createNode(array $definition): Node {
    $node_values = [
      'type' => 'dich_vu',
      'title' => $definition['title'],
      'status' => 1,
      'langcode' => 'vi',
      'body' => $this->textField($definition['body']),
      'path' => [
        'alias' => '/' . $definition['alias'],
        'pathauto' => 0,
      ],
    ];

    if (!empty($definition['hero'])) {
      $node_values['field_image'] = $this->imageField($definition['hero'], $definition['title']);
    }
    if (!empty($definition['intro_image'])) {
      $node_values['field_intro_image'] = $this->imageField($definition['intro_image'], $definition['title']);
    }
    if (!empty($definition['why_image'])) {
      $node_values['field_why_image'] = $this->imageField($definition['why_image'], $definition['title']);
    }

    if (!empty($definition['stats'])) {
      $node_values['field_so_lieu'] = [];
      foreach ($definition['stats'] as $stat) {
        $paragraph = $this->createStat($stat);
        $node_values['field_so_lieu'][] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }
    }

    if (!empty($definition['highlights'])) {
      $node_values['field_khoi_noi_bat'] = [];
      foreach ($definition['highlights'] as $highlight) {
        $paragraph = $this->createHighlight($highlight);
        $node_values['field_khoi_noi_bat'][] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }
    }

    if (!empty($definition['solutions'])) {
      $node_values['field_giai_phap'] = [];
      foreach ($definition['solutions'] as $solution) {
        $paragraph = $this->createSolution($solution);
        $node_values['field_giai_phap'][] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }
    }

    if (!empty($definition['process'])) {
      $node_values['field_quy_trinh'] = [];
      foreach ($definition['process'] as $step) {
        $paragraph = $this->createProcessStep($step);
        $node_values['field_quy_trinh'][] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }
    }

    if (!empty($definition['gallery'])) {
      $node_values['field_hinh_anh_tabs'] = [];
      foreach ($definition['gallery'] as $tab) {
        $paragraph = $this->createGalleryTab($tab);
        $node_values['field_hinh_anh_tabs'][] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }
    }

    if (!empty($definition['why'])) {
      $node_values['field_tai_sao'] = [];
      foreach ($definition['why'] as $item) {
        $paragraph = $this->createWhyItem($item);
        $node_values['field_tai_sao'][] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }
    }

    $project_nids = $this->resolveProjectNids($definition['projects'] ?? []);
    if ($project_nids !== []) {
      $node_values['field_du_an'] = array_map(static fn(int $nid) => ['target_id' => $nid], $project_nids);
    }

    $node = Node::create($node_values);
    $node->save();
    return $node;
  }

  /**
   * Creates a so_lieu_thong_ke paragraph.
   */
  private function createStat(array $stat): Paragraph {
    $paragraph = Paragraph::create([
      'type' => 'so_lieu_thong_ke',
      'field_title' => $stat['value'],
      'field_unit' => $stat['unit'] ?? '',
      'field_content' => $stat['label'],
    ]);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Creates a khoi_noi_bat paragraph with nested muc_noi_bat items.
   */
  private function createHighlight(array $highlight): Paragraph {
    $children = [];
    foreach ($highlight['items'] ?? [] as $item) {
      $child_values = [
        'type' => 'muc_noi_bat',
        'field_title' => $item['title'] ?? '',
        'field_body' => $this->textField($item['body'] ?? ''),
      ];
      if (!empty($item['icon'])) {
        $child_values['field_anh'] = $this->imageField($item['icon']);
      }
      $child = Paragraph::create($child_values);
      $child->save();
      $children[] = [
        'target_id' => $child->id(),
        'target_revision_id' => $child->getRevisionId(),
      ];
    }

    $values = [
      'type' => 'khoi_noi_bat',
      'field_title' => $highlight['title'] ?? '',
      'field_body' => $this->textField($highlight['lead'] ?? ''),
      'field_para_para_content' => $children,
    ];
    if (!empty($highlight['image'])) {
      $values['field_anh'] = $this->imageField($highlight['image'], $highlight['title'] ?? '');
    }

    $paragraph = Paragraph::create($values);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Creates a linh_vuc_hoat_dong paragraph.
   */
  private function createSolution(array $solution): Paragraph {
    $values = [
      'type' => 'linh_vuc_hoat_dong',
      'field_title' => $solution['title'],
      'field_body' => $this->textField($solution['body']),
    ];
    if (!empty($solution['image'])) {
      $values['field_anh'] = $this->imageField($solution['image'], $solution['title']);
    }
    $paragraph = Paragraph::create($values);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Creates a buoc_quy_trinh paragraph.
   */
  private function createProcessStep(array $step): Paragraph {
    $values = [
      'type' => 'buoc_quy_trinh',
      'field_title' => $step['title'],
    ];
    if (!empty($step['body'])) {
      $values['field_body'] = $this->textField('<p>' . $step['body'] . '</p>');
    }
    if (!empty($step['icon'])) {
      $values['field_anh'] = $this->imageField($step['icon'], $step['title']);
    }
    $paragraph = Paragraph::create($values);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Creates a tab_hinh_anh paragraph.
   */
  private function createGalleryTab(array $tab): Paragraph {
    $images = [];
    foreach ($tab['images'] as $path) {
      $field = $this->imageField($path, $tab['title'] ?: 'Gallery');
      if ($field !== NULL) {
        $images[] = $field;
      }
    }

    $paragraph = Paragraph::create([
      'type' => 'tab_hinh_anh',
      'field_title' => $tab['title'] ?? '',
      'field_hinh_anh' => $images,
    ]);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Creates a tai_sao_chon paragraph.
   */
  private function createWhyItem(array $item): Paragraph {
    $paragraph = Paragraph::create([
      'type' => 'tai_sao_chon',
      'field_content' => $item['number'],
      'field_title' => $item['title'],
      'field_body' => $this->textField('<p>' . $item['body'] . '</p>'),
    ]);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Resolves project node IDs by partial title match.
   *
   * @param string[] $search_terms
   *
   * @return int[]
   */
  private function resolveProjectNids(array $search_terms): array {
    $nids = [];
    foreach ($search_terms as $term) {
      $found = \Drupal::entityQuery('node')
        ->condition('type', 'projects')
        ->condition('title', '%' . $term . '%', 'LIKE')
        ->range(0, 1)
        ->accessCheck(FALSE)
        ->execute();
      if ($found !== []) {
        $nid = (int) reset($found);
        if (!in_array($nid, $nids, TRUE)) {
          $nids[] = $nid;
        }
      }
    }
    return $nids;
  }

  /**
   * Checks whether a path alias already exists.
   */
  private function aliasExists(string $alias): bool {
    $storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    $existing = $storage->loadByProperties(['alias' => $alias, 'langcode' => 'vi']);
    return $existing !== [];
  }

  /**
   * Builds a text_long / text_with_summary field value array.
   *
   * @return array{value: string, format: string}
   */
  private function textField(string $value): array {
    return [
      'value' => $value,
      'format' => self::TEXT_FORMAT,
    ];
  }

  /**
   * Recursively updates text field formats on an entity and its paragraphs.
   */
  private function fixEntityTextFormats(object $entity): bool {
    $changed = FALSE;

    foreach ($entity->getFieldDefinitions() as $field_name => $definition) {
      if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
        continue;
      }

      $type = $definition->getType();
      if (in_array($type, ['text', 'text_long', 'text_with_summary'], TRUE)) {
        foreach ($entity->get($field_name) as $item) {
          if ($item->format && $item->format !== self::TEXT_FORMAT) {
            $item->format = self::TEXT_FORMAT;
            $changed = TRUE;
          }
        }
      }
      elseif ($type === 'entity_reference_revisions') {
        foreach ($entity->get($field_name) as $item) {
          $paragraph = $item->entity;
          if ($paragraph && $this->fixEntityTextFormats($paragraph)) {
            $paragraph->save();
            $changed = TRUE;
          }
        }
      }
    }

    return $changed;
  }

  /**
   * Builds an image field item array.
   *
   * @return array<string, mixed>|null
   */
  private function imageField(string $relative_path, string $alt = ''): ?array {
    $fid = $this->ensureFile($relative_path);
    if ($fid === NULL) {
      return NULL;
    }
    return [
      'target_id' => $fid,
      'alt' => $alt,
    ];
  }

  /**
   * Imports a file to public:// and returns its file ID.
   */
  private function ensureFile(string $relative_path): ?int {
    $relative_path = $this->normalizeRelativePath($relative_path);
    if (isset($this->fileCache[$relative_path])) {
      return $this->fileCache[$relative_path];
    }

    $absolute = $this->resolveAbsolutePath($relative_path);
    if ($absolute === NULL || !is_readable($absolute)) {
      return NULL;
    }

    $directory = 'public://dich-vu';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $basename = basename($absolute);
    $destination = $directory . '/' . $basename;
    $data = file_get_contents($absolute);
    if ($data === FALSE) {
      return NULL;
    }

    $existing = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $destination]);
    if ($existing !== []) {
      $file = reset($existing);
      $this->fileCache[$relative_path] = (int) $file->id();
      return $this->fileCache[$relative_path];
    }

    $file = $this->fileRepository->writeData($data, $destination, FileSystemInterface::EXISTS_REPLACE);
    $file->setPermanent();
    $file->save();

    $this->fileCache[$relative_path] = (int) $file->id();
    return $this->fileCache[$relative_path];
  }

  /**
   * Normalizes an HTML images/... path to a relative asset key.
   */
  private function normalizeRelativePath(string $path): string {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#^(\./)+#', '', $path) ?? $path;
    if (str_starts_with($path, 'images/')) {
      $path = substr($path, 7);
    }
    return $path;
  }

  /**
   * Resolves a theme/html image path, applying aliases for missing assets.
   */
  private function resolveAbsolutePath(string $relative): ?string {
    $candidates = [];
    if (isset(self::IMAGE_ALIASES[$relative])) {
      $candidates[] = self::IMAGE_ALIASES[$relative];
    }

    $basename = basename($relative);
    if (isset(self::IMAGE_ALIASES[$basename])) {
      $candidates[] = self::IMAGE_ALIASES[$basename];
    }

    $candidates[] = $relative;
    $candidates[] = 'services/process/' . strtolower($basename);

    $roots = [
      DRUPAL_ROOT . '/html/images/',
      DRUPAL_ROOT . '/themes/cassiopeia_theme/images/',
    ];

    foreach ($candidates as $candidate) {
      foreach ($roots as $root) {
        $absolute = $root . $candidate;
        if (is_readable($absolute)) {
          return $absolute;
        }
      }
    }

    return NULL;
  }

}
