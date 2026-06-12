<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Seeds the home_config node from legacy homepage HTML content.
 */
class HomeConfigContentImporter {

  private const TEXT_FORMAT = 'basic_html';

  private const REGISTRY_KEY = 'home';

  /**
   * @var array<string, int>
   */
  private array $fileCache = [];

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly FileRepositoryInterface $fileRepository,
  ) {}

  /**
   * @return string[]
   */
  public function import(): array {
    $existing = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'home_config')
      ->condition('field_link', self::REGISTRY_KEY)
      ->range(0, 1)
      ->execute();

    if ($existing !== []) {
      return [(string) t('Skipped home_config — already exists.')];
    }

    $node = Node::create([
      'type' => 'home_config',
      'title' => 'Cấu hình trang chủ',
      'status' => 1,
      'langcode' => 'vi',
      'field_link' => self::REGISTRY_KEY,
      'field_about_title' => 'Nhà phát triển công nghiệp hàng đầu với các giải pháp toàn diện',
      'field_about_lead' => [
        'value' => '<strong class="t-subtitle">CNCIndustrial</strong> tập trung phát triển và khai thác hạ tầng khu công nghiệp theo định hướng xanh – thông minh – bền vững, cung cấp hệ sinh thái công nghiệp đồng bộ cho nhà đầu tư trong và ngoài nước',
        'format' => self::TEXT_FORMAT,
      ],
      'field_hoang_sa_label' => "Quần Đảo\nHoàng Sa",
      'field_truong_sa_label' => "Quần Đảo\nTrường Sa",
      'field_services_title' => 'Dịch vụ CNCIndustrial',
      'field_services_lead' => [
        'value' => 'CNCIndustrial tập trung phát triển và đồng hành cùng doanh nghiệp từ giai đoạn chuẩn bị hạ tầng, xây dựng, vận hành đến tối ưu hoạt động sản xuất lâu dài. Với 8 giải pháp dịch vụ chủ lực giúp các doanh nghiệp phát triển mạnh tại Việt Nam.',
        'format' => self::TEXT_FORMAT,
      ],
      'field_projects_title' => "Các dự án công nghiệp\nnổi bật",
      'field_projects_lead' => [
        'value' => 'CNCIndustrial đã đầu tư đáng kể vào bảy tỉnh thành trên khắp Việt Nam. Trong số đó, Phú Thọ (mới) nổi bật là trọng tâm, với 650 ha quỹ đất đã mua lại cho đến nay.',
        'format' => self::TEXT_FORMAT,
      ],
      'field_partners_title' => 'Mạng lưới đối tác & khách hàng',
      'field_customers_title' => 'Khách hàng của chúng tôi',
    ]);

    $map_image = $this->imageField('cnc-map.png', 'Bản đồ các dự án CNC Industrial tại Việt Nam');
    if ($map_image) {
      $node->set('field_image', $map_image);
    }

    $partners_map = $this->imageField('CNC.Map.png', 'Bản đồ mạng lưới khách hàng quốc tế của CNC Industrial');
    if ($partners_map) {
      $node->set('field_image_location', $partners_map);
    }

    $customers_bg = $this->imageField('customers/earth-bg.jpg', '');
    if ($customers_bg) {
      $node->set('field_customers_bg', $customers_bg);
    }

    $node->set('field_so_lieu', $this->buildStatParagraphs());
    $node->set('field_diem_ban_do', $this->buildMapSpotParagraphs());
    $node->set('field_chi_so_doi_tac', $this->buildPartnerStatParagraphs());
    $node->set('field_hang_quoc_gia', $this->buildCountryRowParagraphs());
    $node->set('field_chung_nhan', $this->buildCertificationParagraphs());
    $node->set('field_customer_stories', $this->buildTestimonialParagraphs());
    $node->set('field_anh', $this->buildLogoImages());

    $node->save();

    return [(string) t('Created home_config node (nid @nid).', ['@nid' => $node->id()])];
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function buildStatParagraphs(): array {
    $stats = [
      ['600', '+', 'Tổng mức đầu tư<br>(triệu USD)'],
      ['650', '+', 'Hecta quỹ đất công nghiệp đang ĐT & PT'],
      ['80', '+', 'Tổng diện tích nhà xưởng kho lấp đầy (Ha)'],
      ['95', '%', 'Tỉ lệ lấp đầy trên diện tích xây dựng (%)'],
      ['498', '+', 'Thu hút đầu tư luỹ kế (triệu USD)'],
      ['63', '+', 'Số lượng khách hàng'],
    ];

    $refs = [];
    foreach ($stats as [$value, $unit, $label]) {
      $paragraph = Paragraph::create([
        'type' => 'so_lieu_thong_ke',
        'field_title' => $value,
        'field_unit' => $unit,
        'field_content' => $label,
      ]);
      $paragraph->save();
      $refs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
    return $refs;
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function buildMapSpotParagraphs(): array {
    $spots = [
      ['phu-tho', 'Phú Thọ', '500 ha', TRUE],
      ['ha-noi', 'Hà Nội', '6.5 ha', FALSE],
      ['bac-ninh', 'Bắc Ninh', '68.1 ha', TRUE],
      ['hung-yen', 'Hưng Yên', '10 ha', TRUE],
      ['ninh-binh', 'Ninh Bình', '4.6 ha', TRUE],
      ['quang-ngai', 'Quảng Ngãi', '1.9 ha', FALSE],
      ['da-nang', 'Đà Nẵng', '1 ha', FALSE],
      ['hcm', 'TP. Hồ Chí Minh', '5.2 ha', FALSE],
    ];

    $refs = [];
    foreach ($spots as [$key, $name, $area, $accent]) {
      $paragraph = Paragraph::create([
        'type' => 'diem_ban_do',
        'field_key' => $key,
        'field_title' => $name,
        'field_unit' => $area,
        'field_noi_bat' => $accent ? 1 : 0,
      ]);
      $paragraph->save();
      $refs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
    return $refs;
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function buildPartnerStatParagraphs(): array {
    $stats = [
      ['45', 'Khách hàng quốc tế', 'globe', 'green'],
      ['6+', 'Quốc gia', 'earth-americas', 'red'],
    ];

    $refs = [];
    foreach ($stats as [$value, $label, $icon, $style]) {
      $paragraph = Paragraph::create([
        'type' => 'chi_so_doi_tac',
        'field_title' => $value,
        'field_content' => $label,
        'field_icon' => $icon,
        'field_style' => $style,
      ]);
      $paragraph->save();
      $refs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
    return $refs;
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function buildCountryRowParagraphs(): array {
    $rows = [
      ['Đài Loan (Trung Quốc)', '5', FALSE],
      ['Hàn Quốc', '3', FALSE],
      ['Malaysia', '1', FALSE],
      ['Việt Nam', '7', FALSE],
      ['Trung Quốc', '27', FALSE],
      ['Hong Kong', '2', FALSE],
      ['Tổng cộng', '45', TRUE],
    ];

    $refs = [];
    foreach ($rows as [$country, $count, $bold]) {
      $paragraph = Paragraph::create([
        'type' => 'hang_quoc_gia',
        'field_title' => $country,
        'field_content' => $count,
        'field_noi_bat' => $bold ? 1 : 0,
      ]);
      $paragraph->save();
      $refs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
    return $refs;
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function buildCertificationParagraphs(): array {
    $certs = [
      ['logo-2.png', 'ISO 9001:2015'],
      ['logo-3.png', 'ISO 14001:2015'],
      ['logo-4.png', 'LEED'],
      ['log-1.png', 'IATF 16949:2016'],
    ];

    $refs = [];
    foreach ($certs as [$file, $alt]) {
      $image = $this->imageField($file, $alt);
      if (!$image) {
        continue;
      }
      $paragraph = Paragraph::create([
        'type' => 'chung_nhan',
        'field_anh' => $image,
        'field_title' => $alt,
      ]);
      $paragraph->save();
      $refs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
    return $refs;
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function buildTestimonialParagraphs(): array {
    $stories = [
      [
        'brand' => 'customers/brand-1.png',
        'avatar' => 'customers/avatar-1.jpg',
        'name' => 'Mr. Jackel Lee',
        'role' => 'SVP of Product Supply & Demand',
        'text' => 'Nhờ CNCTech Industrial, việc xây dựng nhà máy của chúng tôi bắt đầu vào tháng 10/2022 và trong vòng 4 tháng, chúng tôi đã sẵn sàng sản xuất. Sự hỗ trợ của họ trên nhiều lĩnh vực, từ đơn xin đầu tư đến hỗ trợ pháp lý, đã đóng vai trò quan trọng trong tiến trình nhanh chóng của chúng tôi.',
      ],
      [
        'brand' => 'customers/brand-2.png',
        'avatar' => 'customers/avatar-2.jpg',
        'name' => 'Mr. Kwon Ki Woon',
        'role' => "Tổng Giám đốc\nNew Protect",
        'text' => 'CNCIndustrial đã đồng hành cùng New Protec Vina xuyên suốt quá trình triển khai nhà máy tại KCN Bá Thiện 1 – từ tư vấn đầu tư, lựa chọn vị trí đến xây dựng và hoàn thiện vận hành. Chỉ trong chưa đầy 1 năm, dự án đã đi vào hoạt động ổn định nhờ tiến độ triển khai nhanh chóng, tối ưu pháp lý và sự phối hợp chuyên nghiệp từ đội ngũ CNCIndustrial. Đây là đối tác đáng tin cậy cho các doanh nghiệp FDI khi đầu tư tại Việt Nam',
      ],
      [
        'brand' => 'customers/brand-3.png',
        'avatar' => 'customers/avatar-3.jpg',
        'name' => 'Mr. Chen Jin Bao',
        'role' => 'CEO of Glitter VietNam',
        'text' => 'Xét đến các lựa chọn như Bắc Ninh và Bắc Giang, Vĩnh Phúc nổi bật với lực lượng lao động ổn định và thủ tục hành chính gọn nhẹ. Vì vậy, chúng tôi đã nhanh chóng đưa ra quyết định chọn Vĩnh Phúc làm nơi khởi đầu cho hoạt động kinh doanh sản xuất tại Việt Nam',
      ],
      [
        'brand' => 'customers/brand-4.png',
        'avatar' => 'customers/avatar-4.jpg',
        'name' => 'Mr. Jason Wu',
        'role' => 'CEO of Sirline VietNam',
        'text' => 'Khi lựa chọn địa điểm gần nhà máy Vĩnh Phúc, các dự án của CNCTech Industrial đã đáp ứng rất tốt nhu cầu về quy mô của chúng tôi. Các dịch vụ toàn diện của họ và môi trường thuận lợi của Vĩnh Phúc đã biến nơi đây thành điểm đến đầu tư lý tưởng cho các doanh nghiệp FDI.',
      ],
    ];

    $refs = [];
    foreach ($stories as $story) {
      $logo = $this->imageField($story['brand'], '');
      $avatar = $this->imageField($story['avatar'], $story['name']);
      $paragraph = Paragraph::create([
        'type' => 'cau_chuyen_khach_hang',
        'field_title' => $story['name'],
        'field_unit' => $story['role'],
        'field_body' => [
          'value' => '<p>' . $story['text'] . '</p>',
          'format' => self::TEXT_FORMAT,
        ],
      ]);
      if ($logo) {
        $paragraph->set('field_anh', $logo);
      }
      if ($avatar) {
        $paragraph->set('field_avatar', $avatar);
      }
      $paragraph->save();
      $refs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
    return $refs;
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function buildLogoImages(): array {
    $logo_dir = DRUPAL_ROOT . '/themes/cassiopeia_theme/images/LOGO';
    if (!is_dir($logo_dir)) {
      return [];
    }

    $images = [];
    $files = scandir($logo_dir);
    if ($files === FALSE) {
      return [];
    }

    foreach ($files as $filename) {
      if ($filename === '.' || $filename === '..') {
        continue;
      }
      $path = 'LOGO/' . $filename;
      $alt = pathinfo($filename, PATHINFO_FILENAME);
      $field = $this->imageField($path, $alt);
      if ($field) {
        $images[] = $field;
      }
    }

    return $images;
  }

  /**
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

  private function ensureFile(string $relative_path): ?int {
    $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');

    if (isset($this->fileCache[$relative_path])) {
      return $this->fileCache[$relative_path];
    }

    $roots = [
      DRUPAL_ROOT . '/themes/cassiopeia_theme/images/',
      DRUPAL_ROOT . '/html/images/',
    ];

    $absolute = NULL;
    foreach ($roots as $root) {
      $candidate = $root . $relative_path;
      if (is_readable($candidate)) {
        $absolute = $candidate;
        break;
      }
    }

    if ($absolute === NULL) {
      return NULL;
    }

    $directory = 'public://home_config';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $destination = $directory . '/' . basename($absolute);
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

}
