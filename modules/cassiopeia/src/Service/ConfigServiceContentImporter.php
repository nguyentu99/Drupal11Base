<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
/**
 * Seeds the config_service node from legacy dich-vu.html content.
 */
class ConfigServiceContentImporter {

  private const TEXT_FORMAT = 'basic_html';

  private const REGISTRY_KEY = 'services';

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
      ->condition('type', 'config_service')
      ->condition('field_link', self::REGISTRY_KEY)
      ->range(0, 1)
      ->execute();

    if ($existing !== []) {
      return [(string) t('Skipped config_service — already exists.')];
    }

    $node = Node::create([
      'type' => 'config_service',
      'title' => 'Giải pháp công nghiệp',
      'status' => 1,
      'langcode' => 'vi',
      'field_link' => self::REGISTRY_KEY,
      'field_intro_title' => 'Giải pháp trọn gói cho công nghiệp',
      'field_intro_lead' => [
        'value' => '<p>Bắt nguồn từ hành trình sản xuất, CNCIndustrial hiểu rằng “Nhà máy” không chỉ là nơi tạo ra sản phẩm, mà còn là không gian kiến tạo công nghệ, giá trị và sự phát triển bền vững cho cộng đồng. Kế thừa kinh nghiệm thực tiễn đó, CNC Industrial tập trung phát triển và khai thác hạ tầng khu công nghiệp theo định hướng <span class="is-green">XANH – THÔNG MINH – BỀN VỮNG</span>, cung cấp hệ sinh thái công nghiệp đồng bộ cho nhà đầu tư trong và ngoài nước.</p><p>Giai đoạn 2023–2025, khối Giải pháp Công nghiệp từng bước định hình chiến lược và tăng trưởng mạnh mẽ; đặc biệt, năm 2025 ghi dấu bước bứt phá đầy bản lĩnh trong bối cảnh kinh tế toàn cầu nhiều biến động.</p>',
        'format' => self::TEXT_FORMAT,
      ],
      'field_milestones_title' => 'Cột mốc quan trọng',
      'field_vision_title' => 'Tầm nhìn - Sứ mệnh',
      'field_leadership_title' => 'Đội ngũ ban lãnh đạo',
      'field_members_title' => 'Công ty thành viên',
      'field_services_title' => 'Dịch vụ của chúng tôi',
      'field_projects_title' => 'dự án của chúng tôi',
    ]);

    $hub_outer = $this->imageField('services-page/hub/ring-outer.png', '');
    if ($hub_outer) {
      $node->set('field_hub_ring_outer', $hub_outer);
    }
    $hub_inner = $this->imageField('inner.png', '');
    if (!$hub_inner) {
      $hub_inner = $this->imageField('innter.png', '');
    }
    if ($hub_inner) {
      $node->set('field_hub_ring_inner', $hub_inner);
    }
    $hub_center = $this->imageField('service-center.png', 'CNC Industrial');
    if (!$hub_center) {
      $hub_center = $this->imageField('services-page/hub/center.png', 'CNC Industrial');
    }
    if ($hub_center) {
      $node->set('field_hub_center', $hub_center);
    }

    $contact_image = $this->imageField('services/contact-panel.png', '');
    if ($contact_image) {
      $node->set('field_contact_image', $contact_image);
    }

    foreach (['video-banner.mp4', 'video-thuml.mp4'] as $video_path) {
      $fid = $this->importVideoFile($video_path);
      if ($fid !== NULL) {
        $node->set('field_video', ['target_id' => $fid]);
        break;
      }
    }

    $node->set('field_so_lieu', $this->buildStatParagraphs());
    $node->set('field_cot_moc', $this->buildMilestoneParagraphs());
    $node->set('field_tam_nhin', $this->buildVisionParagraphs());
    $node->set('field_lanh_dao', $this->buildLeadershipParagraphs());
    $node->set('field_thanh_vien', $this->buildMemberParagraphs());
    $node->set('field_dich_vu', $this->resolveServiceReferences());
    $node->set('field_du_an', $this->resolveProjectReferences());

    $node->save();

    return [(string) t('Created config_service node (nid @nid).', ['@nid' => $node->id()])];
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function buildStatParagraphs(): array {
    $stats = [
      ['services-page/stats/investment.png', '600', '+', 'Tổng mức đầu tư<br>(triệu USD)'],
      ['services-page/stats/land.png', '650', '', 'Tổng diện tích quỹ đất<br>khai thác &amp; phát triển dịch vụ'],
      ['services-page/stats/area.png', '80', '', 'Tổng diện tích dịch vụ cho thuê<br>lấp đầy (ha)'],
      ['services-page/stats/fill-rate.png', '95', '%', 'Tỷ lệ lấp đầy trên diện tích<br>xây dựng'],
      ['services-page/stats/money.png', '498', '', 'Thu hút đầu tư luỹ kế'],
      ['services-page/stats/customers.png', '63', '', 'Số lượng khách hàng trên toàn<br>thế giới'],
    ];

    $refs = [];
    foreach ($stats as [$icon, $value, $unit, $label]) {
      $paragraph = Paragraph::create([
        'type' => 'so_lieu_thong_ke',
        'field_title' => $value,
        'field_unit' => $unit,
        'field_content' => $label,
      ]);
      $image = $this->imageField($icon, '');
      if ($image) {
        $paragraph->set('field_anh', $image);
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
  private function buildMilestoneParagraphs(): array {
    $panels = [
      [
        'period' => '2008 - 2015',
        'summary' => 'Hành trình khởi nghiệp từ một xưởng gia công phần mềm (CAM) đến một nền tảng sản xuất tích hợp.',
        'open' => TRUE,
        'future' => FALSE,
        'entries' => [
          ['2008', 'Nhóm sáng lập bắt đầu hoạt động trong lĩnh vực lập trình CAM, phục vụ khách hàng và dự án xuất khẩu sang thị trường Mỹ.', 'services-page/hub/center.png'],
          ['2010', 'Thành lập và mở xưởng gia công CNC đầu tiên tại tỉnh Bình Dương.', 'services-page/hub/icon-kcn.png'],
          ['2013', 'Thành lập liên doanh với đối tác Nhật Bản trong lĩnh vực gia công cơ khí chính xác.', 'services-page/hub/icon-infra.png'],
          ['2015', 'Thành lập CNCTech Hà Nội và bắt đầu hoạt động trong lĩnh vực chế tạo khuôn mẫu và gia công ép phun nhựa.', 'services-page/hub/icon-building.png'],
        ],
      ],
      [
        'period' => '2016 - 2021',
        'summary' => 'Mở rộng đầu tư và sáp nhập, đầu tư vào các công ty trong lĩnh vực tự động hóa, dầu khí và điện tử, từng bước xây dựng một hệ sinh thái sản xuất tích hợp.',
        'open' => FALSE,
        'future' => FALSE,
        'entries' => [
          ['2018', 'Hoàn tất thương vụ mua bán sáp nhập với VKX (lĩnh vực EMS).<br>Đầu tư vào nhà máy VINAM Oil Tools và SMCTech.', 'services-page/hub/center.png'],
          ['2019', 'Thành lập nhà máy CNCTech Sài Gòn tại thành phố Hồ Chí Minh.<br>Đầu tư vào Skylight, Mentech, Vineco và CNC Vina.<br>Mua lại nhà máy Framas Bắc Ninh.', 'services-page/hub/icon-kcn.png'],
          ['2021', 'Di dời trụ sở chính và cơ sở sản xuất chính đến CNCTech Thăng Long.', 'services-page/hub/icon-infra.png'],
        ],
      ],
      [
        'period' => '2022 - 2025',
        'summary' => 'Tiếp tục mở rộng quy mô, đầu tư vào sự xuất sắc về kỹ thuật và năng lực quản lý, định vị Tập đoàn cho sự phát triển toàn cầu.',
        'open' => FALSE,
        'future' => FALSE,
        'entries' => [
          ['2024', 'Bắt đầu hoạt động với Dịch vụ sản xuất tích hợp.<br>Khởi xướng mở rộng thị trường toàn cầu.<br>Mở rộng năng lực sản xuất.', 'services-page/hub/icon-building.png'],
          ['2025', 'Hoàn tất sáp nhập và mua lại với ASV.<br>Mở rộng thị trường toàn cầu thông qua các triển lãm và quan hệ đối tác chiến lược.', 'services-page/hub/center.png'],
        ],
      ],
      [
        'period' => '2026 - 2030',
        'summary' => 'Tiếp tục mở rộng quy mô, tập trung đầu tư để trở thành nhà cung cấp cấp 1 và cấp 2 cho các nhà sản xuất máy móc trong các ngành công nghiệp bán dẫn, ô tô, thiết bị y tế và hàng không vũ trụ.',
        'open' => FALSE,
        'future' => TRUE,
        'entries' => [],
      ],
    ];

    $refs = [];
    foreach ($panels as $panel) {
      $entry_refs = [];
      foreach ($panel['entries'] as [$year, $text, $image_path]) {
        $entry = Paragraph::create([
          'type' => 'muc_cot_moc',
          'field_title' => $year,
          'field_body' => [
            'value' => '<p>' . $text . '</p>',
            'format' => self::TEXT_FORMAT,
          ],
        ]);
        $image = $this->imageField($image_path, $year);
        if ($image) {
          $entry->set('field_anh', $image);
        }
        $entry->save();
        $entry_refs[] = [
          'target_id' => $entry->id(),
          'target_revision_id' => $entry->getRevisionId(),
        ];
      }

      $paragraph = Paragraph::create([
        'type' => 'cot_moc_dich_vu',
        'field_period' => $panel['period'],
        'field_body' => [
          'value' => '<p>' . $panel['summary'] . '</p>',
          'format' => self::TEXT_FORMAT,
        ],
        'field_noi_bat' => $panel['open'],
        'field_key' => $panel['future'] ? 'future' : '',
        'field_muc_cot_moc' => $entry_refs,
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
  private function buildVisionParagraphs(): array {
    $cards = [
      ['1', 'Tầm nhìn', 'Dẫn đầu hệ sinh thái công nghiệp xanh – thông minh tại Việt Nam.', 'services-page/hub/center.png'],
      ['2', 'Sứ mệnh', 'Kiến tạo tương lai phát triển bền vững và đổi mới trong lĩnh vực công nghiệp và logistics, nâng cao năng lực của Việt Nam trong chuỗi cung ứng toàn cầu.', 'services-page/hub/icon-building.png'],
    ];

    $refs = [];
    foreach ($cards as [$badge, $title, $body, $image_path]) {
      $paragraph = Paragraph::create([
        'type' => 'the_tam_nhin',
        'field_content' => $badge,
        'field_title' => $title,
        'field_body' => [
          'value' => '<p>' . $body . '</p>',
          'format' => self::TEXT_FORMAT,
        ],
      ]);
      $image = $this->imageField($image_path, $title);
      if ($image) {
        $paragraph->set('field_anh', $image);
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
  private function buildLeadershipParagraphs(): array {
    $people = [
      [TRUE, 'Ông', 'Nguyễn Văn Hùng', "Nhà sáng lập\nChủ tịch Hội đồng quản trị\nChủ tịch điều hành", '<p>Nhà sáng lập và Chủ tịch CNC Tech từ năm 2008 đến nay.</p>', 'services-page/hub/center.png', 'services-page/hub/icon-kcn.png'],
      [FALSE, 'Ông', 'Nguyễn Trung Kiên', 'Phó Chủ tịch, Tổng giám đốc tập đoàn CNCTech', '<p>Phó Chủ tịch, Tổng giám đốc tập đoàn CNCTech</p>', 'services-page/hub/icon-infra.png', 'services-page/hub/icon-infra.png'],
      [FALSE, 'Ông', 'Vũ Anh Tuấn', "Phó chủ tịch tập đoàn CNCTech\nTGĐ Công ty TNHH Logistic Bắc Giang", '<p>Phó chủ tịch tập đoàn CNCTech</p><p>TGĐ Công ty TNHH Logistic Bắc Giang</p>', 'services-page/hub/icon-building.png', 'services-page/hub/icon-building.png'],
      [FALSE, 'Bà', 'Đinh Thị Thu Hà', "Phó tổng giám đốc tập đoàn CNCTech\nTổng giám đốc CTCP CNCHoldings", '<p>Phó tổng giám đốc tập đoàn CNCTech</p><p>Tổng giám đốc CTCP CNCHoldings</p>', 'services-page/stats/customers.png', 'services-page/stats/customers.png'],
      [FALSE, 'Bà', 'Nguyễn Phương Nga', "Phó tổng giám đốc tập đoàn CNCTech\nTrợ lý cấp cao Chủ tịch điều hành", '<p>Phó tổng giám đốc tập đoàn CNCTech</p><p>Trợ lý cấp cao Chủ tịch điều hành</p>', 'services-page/stats/money.png', 'services-page/stats/money.png'],
      [FALSE, 'Ông', 'Đinh Hùng Cường', 'Tổng giám đốc CTCP CNCTech Global', '<p>Tổng giám đốc CTCP CNCTech Global</p>', 'services-page/stats/land.png', 'services-page/stats/land.png'],
      [FALSE, 'Ông', 'Trần Ngọc Cường', "Chủ tịch CTCP VinaStartup Vĩnh Phúc\nTổng giám đốc CNCTech Hà Nam", '<p>Chủ tịch CTCP VinaStartup Vĩnh Phúc</p><p>Tổng giám đốc CNCTech Hà Nam</p>', 'services-page/stats/area.png', 'services-page/stats/area.png'],
      [FALSE, 'Ông', 'Phùng Văn Ngọc', 'Tổng giám đốc CTCP VinaStartup Vĩnh Phúc', '<p>Tổng giám đốc CTCP VinaStartup Vĩnh Phúc</p>', 'services-page/stats/fill-rate.png', 'services-page/stats/fill-rate.png'],
      [FALSE, 'Ông', 'Nguyễn Thái Sơn', 'Tổng giám đốc CTCP Arts Group', '<p>Tổng giám đốc CTCP Arts Group</p>', 'services-page/stats/investment.png', 'services-page/stats/investment.png'],
    ];

    $refs = [];
    foreach ($people as [$featured, $prefix, $name, $role, $detail, $thumb, $photo]) {
      $paragraph = Paragraph::create([
        'type' => 'lanh_dao',
        'field_noi_bat' => $featured,
        'field_unit' => $prefix,
        'field_title' => $name,
        'field_content' => $role,
        'field_body' => [
          'value' => $detail,
          'format' => self::TEXT_FORMAT,
        ],
      ]);
      $thumb_image = $this->imageField($thumb, $name);
      if ($thumb_image) {
        $paragraph->set('field_anh', $thumb_image);
      }
      $photo_image = $this->imageField($photo, $name);
      if ($photo_image) {
        $paragraph->set('field_hinh_anh', $photo_image);
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
  private function buildMemberParagraphs(): array {
    $members = [
      ['cnctech', 'CTCP CNCTech<br>global', 'services-page/hub/center.png'],
      ['vina', 'CTCP vina startup<br>vĩnh phúc', 'services-page/hub/icon-kcn.png'],
    ];

    $refs = [];
    foreach ($members as [$key, $name, $logo]) {
      $paragraph = Paragraph::create([
        'type' => 'cong_ty_thanh_vien',
        'field_key' => $key,
        'field_title' => $name,
      ]);
      $image = $this->imageField($logo, $name);
      if ($image) {
        $paragraph->set('field_anh', $image);
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
   * @return array<int, array<string, int>>
   */
  private function resolveServiceReferences(): array {
    $aliases = [
      'thiet-lap-he-thong-ky-thuat-noi-that-nha-xuong',
      'quan-ly-van-hanh-kcn',
      'thiet-ke-xay-dung-cong-trinh-cong-nghiep',
      'thiet-ke-thi-cong-giai-phap-phong-chay-cong-nghiep',
    ];

    $nids = [];
    foreach ($aliases as $alias) {
      $alias_entity = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties(['alias' => '/' . $alias]);
      if ($alias_entity !== []) {
        $entity = reset($alias_entity);
        if (preg_match('/node\/(\d+)/', (string) $entity->getPath(), $matches)) {
          $nids[] = (int) $matches[1];
        }
      }
    }

    if ($nids === []) {
      $nids = array_map('intval', \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'dich_vu')
        ->condition('status', 1)
        ->sort('nid')
        ->execute());
    }

    return array_map(static fn (int $nid): array => ['target_id' => $nid], array_values(array_unique($nids)));
  }

  /**
   * @return array<int, array<string, int>>
   */
  private function resolveProjectReferences(): array {
    $nids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'projects')
      ->condition('status', 1)
      ->sort('nid')
      ->range(0, 8)
      ->execute();

    return array_map(static fn ($nid): array => ['target_id' => (int) $nid], $nids);
  }

  /**
   * Imports a video from theme/html assets into public files.
   */
  public function importVideoFile(string $relative_path): ?int {
    return $this->ensureVideoFile($relative_path);
  }

  /**
   * @return array{target_id: int, alt?: string}|null
   */
  private function imageField(string $relative_path, string $alt): ?array {
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

    $directory = 'public://config_service';
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

  private function ensureVideoFile(string $relative_path): ?int {
    $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');
    $cache_key = 'video:' . $relative_path;

    if (isset($this->fileCache[$cache_key])) {
      return $this->fileCache[$cache_key];
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

    $directory = 'public://config_service/videos';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $destination = $directory . '/' . basename($absolute);
    $data = file_get_contents($absolute);
    if ($data === FALSE) {
      return NULL;
    }

    $existing = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $destination]);
    if ($existing !== []) {
      $file = reset($existing);
      $this->fileCache[$cache_key] = (int) $file->id();
      return $this->fileCache[$cache_key];
    }

    $file = $this->fileRepository->writeData($data, $destination, FileSystemInterface::EXISTS_REPLACE);
    $file->setPermanent();
    $file->save();
    $this->fileCache[$cache_key] = (int) $file->id();
    return $this->fileCache[$cache_key];
  }

}
