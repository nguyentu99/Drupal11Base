<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Installs the home_config content type, fields, and paragraph bundles.
 */
class HomeConfigContentInstaller {

  /**
   * Default image field instance settings (alt optional).
   *
   * @var array<string, mixed>
   */
  private const IMAGE_FIELD_SETTINGS = [
    'file_directory' => '[date:custom:Y]-[date:custom:m]',
    'file_extensions' => 'png gif jpg jpeg webp svg',
    'max_filesize' => '',
    'max_resolution' => '',
    'min_resolution' => '',
    'alt_field' => TRUE,
    'alt_field_required' => FALSE,
    'title_field' => FALSE,
    'title_field_required' => FALSE,
    'default_image' => [
      'uuid' => NULL,
      'alt' => '',
      'title' => '',
      'width' => NULL,
      'height' => NULL,
    ],
  ];

  /**
   * Installs or updates homepage configuration content structures.
   */
  public function install(): void {
    $this->installParagraphTypes();
    $this->installFieldStorages();
    $this->installParagraphFields();
    $this->installNodeType();
    $this->installNodeFields();
    $this->installDisplays();
    $this->relaxImageAltRequirement();
    $this->enableContentTranslation();
  }

  /**
   * Enables content translation for home_config and its paragraph bundles.
   */
  public function enableContentTranslation(): void {
    if (!\Drupal::moduleHandler()->moduleExists('content_translation')) {
      return;
    }

    /** @var \Drupal\content_translation\ContentTranslationManagerInterface $manager */
    $manager = \Drupal::service('content_translation.manager');

    $bundles = [
      ['node', 'home_config'],
      ['paragraph', 'so_lieu_thong_ke'],
      ['paragraph', 'diem_ban_do'],
      ['paragraph', 'chi_so_doi_tac'],
      ['paragraph', 'hang_quoc_gia'],
      ['paragraph', 'chung_nhan'],
      ['paragraph', 'cau_chuyen_khach_hang'],
    ];

    foreach ($bundles as [$entity_type, $bundle]) {
      if (!$manager->isSupported($entity_type)) {
        continue;
      }
      $manager->setEnabled($entity_type, $bundle, TRUE);
      $manager->setBundleTranslationSettings($entity_type, $bundle, [
        'untranslatable_fields_hide' => '0',
      ]);
    }
  }

  /**
   * Ensures alt text is optional on image fields used by home_config.
   */
  public function relaxImageAltRequirement(): void {
    $fields = [
      ['node', 'home_config', 'field_image'],
      ['node', 'home_config', 'field_image_location'],
      ['node', 'home_config', 'field_customers_bg'],
      ['node', 'home_config', 'field_anh'],
      ['paragraph', 'chung_nhan', 'field_anh'],
      ['paragraph', 'cau_chuyen_khach_hang', 'field_anh'],
      ['paragraph', 'cau_chuyen_khach_hang', 'field_avatar'],
    ];

    foreach ($fields as [$entity_type, $bundle, $field_name]) {
      $field = FieldConfig::load("$entity_type.$bundle.$field_name");
      if (!$field || $field->getType() !== 'image') {
        continue;
      }
      $settings = $field->getSettings();
      if (!empty($settings['alt_field_required'])) {
        $settings['alt_field_required'] = FALSE;
        $field->setSettings($settings);
        $field->save();
      }
    }
  }

  /**
   * Creates paragraph bundles used on the home config node.
   */
  protected function installParagraphTypes(): void {
    $types = [
      'diem_ban_do' => 'Điểm bản đồ',
      'chi_so_doi_tac' => 'Chỉ số đối tác',
      'hang_quoc_gia' => 'Hàng quốc gia (bảng đối tác)',
      'chung_nhan' => 'Chứng nhận',
    ];

    foreach ($types as $id => $label) {
      if (ParagraphsType::load($id)) {
        continue;
      }
      ParagraphsType::create([
        'id' => $id,
        'label' => $label,
      ])->save();
    }

    if (!ParagraphsType::load('so_lieu_thong_ke')) {
      ParagraphsType::create([
        'id' => 'so_lieu_thong_ke',
        'label' => 'Số liệu thống kê',
      ])->save();
    }

    if (!ParagraphsType::load('cau_chuyen_khach_hang')) {
      ParagraphsType::create([
        'id' => 'cau_chuyen_khach_hang',
        'label' => 'Câu chuyện khách hàng',
      ])->save();
    }
  }

  /**
   * Creates field storage definitions for home_config.
   */
  protected function installFieldStorages(): void {
    $node_storages = [
      'field_about_title' => ['type' => 'string', 'cardinality' => 1],
      'field_about_lead' => ['type' => 'text_long', 'cardinality' => 1],
      'field_services_title' => ['type' => 'string', 'cardinality' => 1],
      'field_services_lead' => ['type' => 'text_long', 'cardinality' => 1],
      'field_projects_title' => ['type' => 'string', 'cardinality' => 1],
      'field_projects_lead' => ['type' => 'text_long', 'cardinality' => 1],
      'field_partners_title' => ['type' => 'string', 'cardinality' => 1],
      'field_customers_title' => ['type' => 'string', 'cardinality' => 1],
      'field_hoang_sa_label' => ['type' => 'string', 'cardinality' => 1],
      'field_truong_sa_label' => ['type' => 'string', 'cardinality' => 1],
      'field_diem_ban_do' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
      'field_chi_so_doi_tac' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
      'field_hang_quoc_gia' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
      'field_chung_nhan' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
      'field_customers_bg' => [
        'type' => 'image',
        'cardinality' => 1,
        'settings' => ['uri_scheme' => 'public'],
      ],
    ];

    foreach ($node_storages as $field_name => $definition) {
      if (FieldStorageConfig::loadByName('node', $field_name)) {
        continue;
      }
      FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'type' => $definition['type'],
        'cardinality' => $definition['cardinality'],
        'settings' => $definition['settings'] ?? [],
        'translatable' => TRUE,
      ])->save();
    }

    if (!FieldStorageConfig::loadByName('node', 'field_so_lieu')) {
      FieldStorageConfig::create([
        'field_name' => 'field_so_lieu',
        'entity_type' => 'node',
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
        'translatable' => TRUE,
      ])->save();
    }

    $paragraph_storages = [
      'field_key' => ['type' => 'string', 'cardinality' => 1],
      'field_noi_bat' => ['type' => 'boolean', 'cardinality' => 1],
      'field_icon' => ['type' => 'string', 'cardinality' => 1],
      'field_style' => ['type' => 'list_string', 'cardinality' => 1],
    ];

    foreach ($paragraph_storages as $field_name => $definition) {
      if (FieldStorageConfig::loadByName('paragraph', $field_name)) {
        continue;
      }
      $storage = [
        'field_name' => $field_name,
        'entity_type' => 'paragraph',
        'type' => $definition['type'],
        'cardinality' => $definition['cardinality'],
        'translatable' => TRUE,
      ];
      if ($field_name === 'field_style') {
        $storage['settings'] = [
          'allowed_values' => [
            'green' => 'Xanh lá',
            'red' => 'Đỏ',
          ],
        ];
      }
      FieldStorageConfig::create($storage)->save();
    }
  }

  /**
   * Attaches fields to paragraph bundles.
   */
  protected function installParagraphFields(): void {
    $definitions = [
      'diem_ban_do' => [
        'field_key' => ['label' => 'Mã CSS (vd: phu-tho)', 'type' => 'string'],
        'field_title' => ['label' => 'Tên tỉnh/thành', 'type' => 'string'],
        'field_unit' => ['label' => 'Diện tích', 'type' => 'string'],
        'field_noi_bat' => ['label' => 'Nhấn mạnh', 'type' => 'boolean'],
      ],
      'chi_so_doi_tac' => [
        'field_title' => ['label' => 'Giá trị', 'type' => 'string'],
        'field_content' => ['label' => 'Nhãn', 'type' => 'string'],
        'field_icon' => ['label' => 'Icon (vd: globe)', 'type' => 'string'],
        'field_style' => ['label' => 'Kiểu màu', 'type' => 'list_string'],
      ],
      'hang_quoc_gia' => [
        'field_title' => ['label' => 'Quốc gia', 'type' => 'string'],
        'field_content' => ['label' => 'Số khách hàng', 'type' => 'string'],
        'field_noi_bat' => ['label' => 'In đậm (dòng tổng)', 'type' => 'boolean'],
      ],
      'chung_nhan' => [
        'field_anh' => ['label' => 'Huy hiệu', 'type' => 'image'],
        'field_title' => ['label' => 'Mô tả / alt', 'type' => 'string'],
      ],
      'so_lieu_thong_ke' => [
        'field_title' => ['label' => 'Giá trị', 'type' => 'string'],
        'field_unit' => ['label' => 'Đơn vị', 'type' => 'string'],
        'field_content' => ['label' => 'Nhãn', 'type' => 'string'],
      ],
      'cau_chuyen_khach_hang' => [
        'field_anh' => ['label' => 'Logo thương hiệu', 'type' => 'image'],
        'field_avatar' => ['label' => 'Ảnh đại diện', 'type' => 'image'],
        'field_title' => ['label' => 'Họ tên', 'type' => 'string'],
        'field_unit' => ['label' => 'Chức danh', 'type' => 'string'],
        'field_body' => ['label' => 'Nội dung', 'type' => 'text_long'],
      ],
    ];

    foreach ($definitions as $bundle => $fields) {
      foreach ($fields as $field_name => $info) {
        $this->ensureField('paragraph', $bundle, $field_name, $info);
      }
    }
  }

  /**
   * Creates the home_config node type.
   */
  protected function installNodeType(): void {
    if (NodeType::load('home_config')) {
      return;
    }

    NodeType::create([
      'type' => 'home_config',
      'name' => 'Cấu hình trang chủ',
      'description' => 'Nội dung tĩnh các khối trên trang chủ (giới thiệu, đối tác, khách hàng, chứng nhận).',
      'new_revision' => TRUE,
      'preview_mode' => DRUPAL_DISABLED,
      'display_submitted' => FALSE,
    ])->save();
  }

  /**
   * Attaches fields to the home_config node bundle.
   */
  protected function installNodeFields(): void {
    $this->ensureField('node', 'home_config', 'field_link', [
      'label' => 'Khóa đăng ký',
      'type' => 'string',
      'settings' => ['max_length' => 64],
    ]);

    $text_fields = [
      'field_about_title' => 'Tiêu đề giới thiệu',
      'field_services_title' => 'Tiêu đề khối dịch vụ',
      'field_projects_title' => 'Tiêu đề khối dự án',
      'field_partners_title' => 'Tiêu đề khối đối tác',
      'field_customers_title' => 'Tiêu đề khối khách hàng',
      'field_hoang_sa_label' => 'Nhãn Hoàng Sa',
      'field_truong_sa_label' => 'Nhãn Trường Sa',
    ];
    foreach ($text_fields as $field_name => $label) {
      $this->ensureField('node', 'home_config', $field_name, [
        'label' => $label,
        'type' => 'string',
      ]);
    }

    foreach ([
      'field_about_lead' => 'Mô tả giới thiệu',
      'field_services_lead' => 'Mô tả khối dịch vụ',
      'field_projects_lead' => 'Mô tả khối dự án',
    ] as $field_name => $label) {
      $this->ensureField('node', 'home_config', $field_name, [
        'label' => $label,
        'type' => 'text_long',
        'settings' => ['allowed_formats' => ['basic_html']],
      ]);
    }

    $image_fields = [
      'field_image' => 'Ảnh bản đồ Việt Nam',
      'field_image_location' => 'Ảnh bản đồ mạng lưới đối tác',
      'field_customers_bg' => 'Ảnh nền khối khách hàng',
      'field_anh' => 'Logo khách hàng',
    ];
    foreach ($image_fields as $field_name => $label) {
      $this->ensureField('node', 'home_config', $field_name, [
        'label' => $label,
        'type' => 'image',
      ]);
    }

    $paragraph_fields = [
      'field_so_lieu' => [
        'label' => 'Số liệu thống kê',
        'target_bundles' => ['so_lieu_thong_ke' => 'so_lieu_thong_ke'],
      ],
      'field_diem_ban_do' => [
        'label' => 'Điểm trên bản đồ',
        'target_bundles' => ['diem_ban_do' => 'diem_ban_do'],
      ],
      'field_chi_so_doi_tac' => [
        'label' => 'Chỉ số đối tác',
        'target_bundles' => ['chi_so_doi_tac' => 'chi_so_doi_tac'],
      ],
      'field_hang_quoc_gia' => [
        'label' => 'Bảng quốc gia',
        'target_bundles' => ['hang_quoc_gia' => 'hang_quoc_gia'],
      ],
      'field_chung_nhan' => [
        'label' => 'Chứng nhận',
        'target_bundles' => ['chung_nhan' => 'chung_nhan'],
      ],
      'field_customer_stories' => [
        'label' => 'Câu chuyện khách hàng',
        'target_bundles' => ['cau_chuyen_khach_hang' => 'cau_chuyen_khach_hang'],
      ],
    ];

    foreach ($paragraph_fields as $field_name => $info) {
      $this->ensureField('node', 'home_config', $field_name, [
        'label' => $info['label'],
        'type' => 'entity_reference_revisions',
        'settings' => [
          'handler' => 'default:paragraph',
          'handler_settings' => [
            'target_bundles' => $info['target_bundles'],
            'negate' => 0,
          ],
        ],
      ]);
    }
  }

  /**
   * Creates form and view displays.
   */
  protected function installDisplays(): void {
    $display = EntityFormDisplay::load('node.home_config.default');
    if (!$display) {
      $display = EntityFormDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'home_config',
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    $weight = 0;
    $components = [
      'title' => 'string_textfield',
      'langcode' => 'language_select',
      'field_link' => 'string_textfield',
      'field_about_title' => 'string_textfield',
      'field_about_lead' => 'text_textarea',
      'field_so_lieu' => 'paragraphs',
      'field_image' => 'image_image',
      'field_diem_ban_do' => 'paragraphs',
      'field_hoang_sa_label' => 'string_textfield',
      'field_truong_sa_label' => 'string_textfield',
      'field_services_title' => 'string_textfield',
      'field_services_lead' => 'text_textarea',
      'field_projects_title' => 'string_textfield',
      'field_projects_lead' => 'text_textarea',
      'field_partners_title' => 'string_textfield',
      'field_chi_so_doi_tac' => 'paragraphs',
      'field_hang_quoc_gia' => 'paragraphs',
      'field_image_location' => 'image_image',
      'field_customers_title' => 'string_textfield',
      'field_customers_bg' => 'image_image',
      'field_anh' => 'image_image',
      'field_customer_stories' => 'paragraphs',
      'field_chung_nhan' => 'paragraphs',
      'status' => 'boolean_checkbox',
    ];

    foreach ($components as $name => $type) {
      $settings = [];
      if ($type === 'text_textarea') {
        $settings['rows'] = 4;
      }
      if ($type === 'paragraphs') {
        $settings = [
          'title' => 'Đoạn',
          'title_plural' => 'Đoạn',
          'edit_mode' => 'open',
          'closed_mode' => 'summary',
          'autocollapse' => 'none',
          'add_mode' => 'dropdown',
          'form_display_mode' => 'default',
        ];
      }
      $display->setComponent($name, [
        'type' => $type,
        'weight' => $weight++,
        'region' => 'content',
        'settings' => $settings,
      ]);
    }
    $display->save();

    $view = EntityViewDisplay::load('node.home_config.default');
    if (!$view) {
      EntityViewDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'home_config',
        'mode' => 'default',
        'status' => TRUE,
      ])->save();
    }

    $paragraph_displays = [
      'diem_ban_do' => [
        'field_key' => 'string_textfield',
        'field_title' => 'string_textfield',
        'field_unit' => 'string_textfield',
        'field_noi_bat' => 'boolean_checkbox',
      ],
      'chi_so_doi_tac' => [
        'field_title' => 'string_textfield',
        'field_content' => 'string_textfield',
        'field_icon' => 'string_textfield',
        'field_style' => 'options_select',
      ],
      'hang_quoc_gia' => [
        'field_title' => 'string_textfield',
        'field_content' => 'string_textfield',
        'field_noi_bat' => 'boolean_checkbox',
      ],
      'chung_nhan' => [
        'field_anh' => 'image_image',
        'field_title' => 'string_textfield',
      ],
      'so_lieu_thong_ke' => [
        'field_title' => 'string_textfield',
        'field_unit' => 'string_textfield',
        'field_content' => 'string_textfield',
      ],
      'cau_chuyen_khach_hang' => [
        'field_anh' => 'image_image',
        'field_avatar' => 'image_image',
        'field_title' => 'string_textfield',
        'field_unit' => 'string_textfield',
        'field_body' => 'text_textarea',
      ],
    ];

    foreach ($paragraph_displays as $bundle => $fields) {
      $pdisplay = EntityFormDisplay::load("paragraph.$bundle.default");
      if (!$pdisplay) {
        $pdisplay = EntityFormDisplay::create([
          'targetEntityType' => 'paragraph',
          'bundle' => $bundle,
          'mode' => 'default',
          'status' => TRUE,
        ]);
      }
      $w = 0;
      foreach ($fields as $field_name => $type) {
        $settings = $type === 'text_textarea' ? ['rows' => 5] : [];
        $pdisplay->setComponent($field_name, [
          'type' => $type,
          'weight' => $w++,
          'region' => 'content',
          'settings' => $settings,
        ]);
      }
      $pdisplay->save();
    }
  }

  /**
   * Ensures a field instance exists on a bundle.
   *
   * @param array<string, mixed> $info
   */
  protected function ensureField(string $entity_type, string $bundle, string $field_name, array $info): void {
    $field_id = "$entity_type.$bundle.$field_name";
    if (FieldConfig::load($field_id)) {
      return;
    }

    $values = [
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $info['label'],
      'required' => FALSE,
      'translatable' => TRUE,
    ];

    if (($info['type'] ?? '') === 'image') {
      $values['settings'] = self::IMAGE_FIELD_SETTINGS;
    }

    if (!empty($info['settings'])) {
      $values['settings'] = array_merge($values['settings'] ?? [], $info['settings']);
    }

    FieldConfig::create($values)->save();
  }

}
