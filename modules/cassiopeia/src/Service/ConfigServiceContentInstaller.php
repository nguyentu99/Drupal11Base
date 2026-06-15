<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Installs the config_service content type, fields, and paragraph bundles.
 */
class ConfigServiceContentInstaller {

  /**
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
   * @var array<string, mixed>
   */
  private const VIDEO_FIELD_SETTINGS = [
    'file_directory' => 'config_service/videos',
    'file_extensions' => 'mp4 webm ogg',
    'max_filesize' => '',
    'handler' => 'default:file',
    'handler_settings' => [],
  ];

  /**
   * Installs or updates services landing page content structures.
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
   * Replaces legacy field_video_path with a file field (field_video).
   *
   * @return string[]
   */
  public function migrateHeroVideoToFile(): array {
    $messages = [];

    if (!FieldStorageConfig::loadByName('node', 'field_video')) {
      FieldStorageConfig::create([
        'field_name' => 'field_video',
        'entity_type' => 'node',
        'type' => 'file',
        'cardinality' => 1,
        'settings' => ['target_type' => 'file', 'display_field' => FALSE, 'display_default' => FALSE],
        'translatable' => TRUE,
      ])->save();
      $messages[] = (string) t('Created field_video storage.');
    }

    $this->ensureField('node', 'config_service', 'field_video', [
      'label' => 'Video hero',
      'type' => 'file',
      'settings' => self::VIDEO_FIELD_SETTINGS,
    ]);

    $display = EntityFormDisplay::load('node.config_service.default');
    if ($display) {
      $display->setComponent('field_video', [
        'type' => 'file_generic',
        'weight' => 3,
        'region' => 'content',
        'settings' => [],
      ]);
    }

    $nids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'config_service')
      ->execute();

    if ($nids !== []) {
      /** @var \Drupal\cassiopeia\Service\ConfigServiceContentImporter $importer */
      $importer = \Drupal::service('cassiopeia.config_service_content_importer');
      /** @var \Drupal\node\NodeInterface[] $nodes */
      $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
      foreach ($nids as $nid) {
        $node = $nodes[$nid] ?? NULL;
        if (!$node || !$node->hasField('field_video') || !$node->get('field_video')->isEmpty()) {
          continue;
        }

        $legacy_path = $node->hasField('field_video_path')
          ? trim((string) ($node->get('field_video_path')->value ?? ''))
          : '';
        $candidates = array_filter([
          $legacy_path !== '' ? ltrim($legacy_path, '/') : NULL,
          'video-banner.mp4',
          'video-thuml.mp4',
        ]);

        foreach ($candidates as $relative) {
          $relative = preg_replace('#^themes/cassiopeia_theme/images/#', '', $relative) ?? $relative;
          $fid = $importer->importVideoFile($relative);
          if ($fid !== NULL) {
            $node->set('field_video', ['target_id' => $fid]);
            $node->save();
            $messages[] = (string) t('Assigned hero video to config_service node @nid.', ['@nid' => $nid]);
            break;
          }
        }
      }
    }

    if ($display) {
      $display->removeComponent('field_video_path');
      $display->save();
    }

    $legacy_field = FieldConfig::load('node.config_service.field_video_path');
    if ($legacy_field) {
      $legacy_field->delete();
      $messages[] = (string) t('Removed field_video_path from config_service.');
    }

    if (FieldStorageConfig::loadByName('node', 'field_video_path')) {
      FieldStorageConfig::loadByName('node', 'field_video_path')->delete();
      $messages[] = (string) t('Removed field_video_path storage.');
    }

    if ($messages === []) {
      $messages[] = (string) t('Hero video file field is ready on config_service.');
    }

    return $messages;
  }

  /**
   * Enables content translation for config_service and paragraph bundles.
   */
  public function enableContentTranslation(): void {
    if (!\Drupal::moduleHandler()->moduleExists('content_translation')) {
      return;
    }

    /** @var \Drupal\content_translation\ContentTranslationManagerInterface $manager */
    $manager = \Drupal::service('content_translation.manager');

    $bundles = [
      ['node', 'config_service'],
      ['paragraph', 'so_lieu_thong_ke'],
      ['paragraph', 'cot_moc_dich_vu'],
      ['paragraph', 'muc_cot_moc'],
      ['paragraph', 'the_tam_nhin'],
      ['paragraph', 'lanh_dao'],
      ['paragraph', 'cong_ty_thanh_vien'],
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
   * Ensures alt text is optional on image fields used by config_service.
   */
  public function relaxImageAltRequirement(): void {
    $fields = [
      ['node', 'config_service', 'field_hub_ring_outer'],
      ['node', 'config_service', 'field_hub_ring_inner'],
      ['node', 'config_service', 'field_hub_center'],
      ['node', 'config_service', 'field_contact_image'],
      ['paragraph', 'so_lieu_thong_ke', 'field_anh'],
      ['paragraph', 'muc_cot_moc', 'field_anh'],
      ['paragraph', 'the_tam_nhin', 'field_anh'],
      ['paragraph', 'lanh_dao', 'field_anh'],
      ['paragraph', 'lanh_dao', 'field_hinh_anh'],
      ['paragraph', 'cong_ty_thanh_vien', 'field_anh'],
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
   * Adds icon support to stat paragraphs used on the services page.
   */
  public function ensureStatIcons(): void {
    if (!ParagraphsType::load('so_lieu_thong_ke')) {
      ParagraphsType::create([
        'id' => 'so_lieu_thong_ke',
        'label' => 'Số liệu thống kê',
      ])->save();
    }

    if (!FieldStorageConfig::loadByName('paragraph', 'field_anh')) {
      FieldStorageConfig::create([
        'field_name' => 'field_anh',
        'entity_type' => 'paragraph',
        'type' => 'image',
        'cardinality' => 1,
        'settings' => ['uri_scheme' => 'public'],
        'translatable' => TRUE,
      ])->save();
    }

    $this->ensureField('paragraph', 'so_lieu_thong_ke', 'field_anh', [
      'label' => 'Icon',
      'type' => 'image',
    ]);

    $display = EntityFormDisplay::load('paragraph.so_lieu_thong_ke.default');
    if ($display) {
      $display->setComponent('field_anh', [
        'type' => 'image_image',
        'weight' => 0,
        'region' => 'content',
        'settings' => [],
      ])->save();
    }
  }

  protected function installParagraphTypes(): void {
    $types = [
      'cot_moc_dich_vu' => 'Cột mốc (trang dịch vụ)',
      'muc_cot_moc' => 'Mục cột mốc',
      'the_tam_nhin' => 'Thẻ tầm nhìn / sứ mệnh',
      'lanh_dao' => 'Ban lãnh đạo',
      'cong_ty_thanh_vien' => 'Công ty thành viên',
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

    $this->ensureStatIcons();
  }

  protected function installFieldStorages(): void {
    $node_storages = [
      'field_intro_title' => ['type' => 'string', 'cardinality' => 1],
      'field_intro_lead' => ['type' => 'text_long', 'cardinality' => 1],
      'field_video' => [
        'type' => 'file',
        'cardinality' => 1,
        'settings' => ['target_type' => 'file', 'display_field' => FALSE, 'display_default' => FALSE],
      ],
      'field_milestones_title' => ['type' => 'string', 'cardinality' => 1],
      'field_vision_title' => ['type' => 'string', 'cardinality' => 1],
      'field_leadership_title' => ['type' => 'string', 'cardinality' => 1],
      'field_members_title' => ['type' => 'string', 'cardinality' => 1],
      'field_services_title' => ['type' => 'string', 'cardinality' => 1],
      'field_projects_title' => ['type' => 'string', 'cardinality' => 1],
      'field_hub_ring_outer' => [
        'type' => 'image',
        'cardinality' => 1,
        'settings' => ['uri_scheme' => 'public'],
      ],
      'field_hub_ring_inner' => [
        'type' => 'image',
        'cardinality' => 1,
        'settings' => ['uri_scheme' => 'public'],
      ],
      'field_hub_center' => [
        'type' => 'image',
        'cardinality' => 1,
        'settings' => ['uri_scheme' => 'public'],
      ],
      'field_contact_image' => [
        'type' => 'image',
        'cardinality' => 1,
        'settings' => ['uri_scheme' => 'public'],
      ],
      'field_cot_moc' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
      'field_tam_nhin' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
      'field_lanh_dao' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
      'field_thanh_vien' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
      'field_dich_vu' => [
        'type' => 'entity_reference',
        'cardinality' => -1,
        'settings' => ['target_type' => 'node'],
      ],
      'field_du_an' => [
        'type' => 'entity_reference',
        'cardinality' => -1,
        'settings' => ['target_type' => 'node'],
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
      'field_period' => ['type' => 'string', 'cardinality' => 1],
      'field_muc_cot_moc' => [
        'type' => 'entity_reference_revisions',
        'cardinality' => -1,
        'settings' => ['target_type' => 'paragraph'],
      ],
    ];

    foreach ($paragraph_storages as $field_name => $definition) {
      if (FieldStorageConfig::loadByName('paragraph', $field_name)) {
        continue;
      }
      FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'paragraph',
        'type' => $definition['type'],
        'cardinality' => $definition['cardinality'],
        'settings' => $definition['settings'] ?? [],
        'translatable' => TRUE,
      ])->save();
    }
  }

  protected function installParagraphFields(): void {
    $definitions = [
      'cot_moc_dich_vu' => [
        'field_period' => ['label' => 'Giai đoạn', 'type' => 'string'],
        'field_body' => ['label' => 'Tóm tắt', 'type' => 'text_long', 'settings' => ['allowed_formats' => ['basic_html']]],
        'field_noi_bat' => ['label' => 'Mở mặc định', 'type' => 'boolean'],
        'field_key' => ['label' => 'Mã (future = giai đoạn tương lai)', 'type' => 'string'],
        'field_muc_cot_moc' => [
          'label' => 'Các mốc',
          'type' => 'entity_reference_revisions',
          'settings' => [
            'handler' => 'default:paragraph',
            'handler_settings' => [
              'target_bundles' => ['muc_cot_moc' => 'muc_cot_moc'],
              'negate' => 0,
            ],
          ],
        ],
      ],
      'muc_cot_moc' => [
        'field_title' => ['label' => 'Năm', 'type' => 'string'],
        'field_body' => ['label' => 'Nội dung', 'type' => 'text_long', 'settings' => ['allowed_formats' => ['basic_html']]],
        'field_anh' => ['label' => 'Ảnh', 'type' => 'image'],
      ],
      'the_tam_nhin' => [
        'field_anh' => ['label' => 'Ảnh', 'type' => 'image'],
        'field_title' => ['label' => 'Tiêu đề', 'type' => 'string'],
        'field_body' => ['label' => 'Mô tả', 'type' => 'text_long', 'settings' => ['allowed_formats' => ['basic_html']]],
        'field_content' => ['label' => 'Số thứ tự', 'type' => 'string'],
      ],
      'lanh_dao' => [
        'field_anh' => ['label' => 'Ảnh thumbnail', 'type' => 'image'],
        'field_hinh_anh' => ['label' => 'Ảnh chi tiết', 'type' => 'image'],
        'field_unit' => ['label' => 'Danh xưng', 'type' => 'string'],
        'field_title' => ['label' => 'Họ tên', 'type' => 'string'],
        'field_content' => ['label' => 'Chức danh (tóm tắt)', 'type' => 'string'],
        'field_body' => ['label' => 'Tiểu sử chi tiết', 'type' => 'text_long', 'settings' => ['allowed_formats' => ['basic_html']]],
        'field_noi_bat' => ['label' => 'Nổi bật', 'type' => 'boolean'],
      ],
      'cong_ty_thanh_vien' => [
        'field_anh' => ['label' => 'Logo', 'type' => 'image'],
        'field_title' => ['label' => 'Tên công ty', 'type' => 'string'],
        'field_key' => ['label' => 'Mã CSS (vd: cnctech, vina)', 'type' => 'string'],
      ],
    ];

    foreach ($definitions as $bundle => $fields) {
      foreach ($fields as $field_name => $info) {
        $this->ensureField('paragraph', $bundle, $field_name, $info);
      }
    }
  }

  protected function installNodeType(): void {
    if (NodeType::load('config_service')) {
      return;
    }

    NodeType::create([
      'type' => 'config_service',
      'name' => 'Cấu hình trang dịch vụ',
      'description' => 'Nội dung trang Giải pháp công nghiệp (/dich-vu).',
      'new_revision' => TRUE,
      'preview_mode' => DRUPAL_DISABLED,
      'display_submitted' => FALSE,
    ])->save();
  }

  protected function installNodeFields(): void {
    $this->ensureField('node', 'config_service', 'field_link', [
      'label' => 'Khóa đăng ký',
      'type' => 'string',
      'settings' => ['max_length' => 64],
    ]);

    $text_fields = [
      'field_intro_title' => 'Tiêu đề giới thiệu',
      'field_milestones_title' => 'Tiêu đề cột mốc',
      'field_vision_title' => 'Tiêu đề tầm nhìn',
      'field_leadership_title' => 'Tiêu đề ban lãnh đạo',
      'field_members_title' => 'Tiêu đề công ty thành viên',
      'field_services_title' => 'Tiêu đề khối dịch vụ',
      'field_projects_title' => 'Tiêu đề khối dự án',
    ];
    foreach ($text_fields as $field_name => $label) {
      $this->ensureField('node', 'config_service', $field_name, [
        'label' => $label,
        'type' => 'string',
      ]);
    }

    $this->ensureField('node', 'config_service', 'field_intro_lead', [
      'label' => 'Mô tả giới thiệu',
      'type' => 'text_long',
      'settings' => ['allowed_formats' => ['basic_html']],
    ]);

    $this->ensureField('node', 'config_service', 'field_video', [
      'label' => 'Video hero',
      'type' => 'file',
      'settings' => self::VIDEO_FIELD_SETTINGS,
    ]);

    foreach ([
      'field_hub_ring_outer' => 'Ảnh vòng ngoài hub',
      'field_hub_ring_inner' => 'Ảnh vòng trong hub',
      'field_hub_center' => 'Ảnh trung tâm hub',
      'field_contact_image' => 'Ảnh form liên hệ',
    ] as $field_name => $label) {
      $this->ensureField('node', 'config_service', $field_name, [
        'label' => $label,
        'type' => 'image',
      ]);
    }

    $paragraph_fields = [
      'field_so_lieu' => [
        'label' => 'Số liệu thống kê',
        'target_bundles' => ['so_lieu_thong_ke' => 'so_lieu_thong_ke'],
      ],
      'field_cot_moc' => [
        'label' => 'Cột mốc',
        'target_bundles' => ['cot_moc_dich_vu' => 'cot_moc_dich_vu'],
      ],
      'field_tam_nhin' => [
        'label' => 'Tầm nhìn / sứ mệnh',
        'target_bundles' => ['the_tam_nhin' => 'the_tam_nhin'],
      ],
      'field_lanh_dao' => [
        'label' => 'Ban lãnh đạo',
        'target_bundles' => ['lanh_dao' => 'lanh_dao'],
      ],
      'field_thanh_vien' => [
        'label' => 'Công ty thành viên',
        'target_bundles' => ['cong_ty_thanh_vien' => 'cong_ty_thanh_vien'],
      ],
    ];

    foreach ($paragraph_fields as $field_name => $info) {
      $this->ensureField('node', 'config_service', $field_name, [
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

    $this->ensureField('node', 'config_service', 'field_dich_vu', [
      'label' => 'Dịch vụ hiển thị trên hub',
      'type' => 'entity_reference',
      'settings' => [
        'handler' => 'default:node',
        'handler_settings' => [
          'target_bundles' => ['dich_vu' => 'dich_vu'],
          'sort' => ['field' => '_none'],
          'auto_create' => FALSE,
        ],
      ],
    ]);

    $this->ensureField('node', 'config_service', 'field_du_an', [
      'label' => 'Dự án nổi bật',
      'type' => 'entity_reference',
      'settings' => [
        'handler' => 'default:node',
        'handler_settings' => [
          'target_bundles' => ['projects' => 'projects'],
          'sort' => ['field' => '_none'],
          'auto_create' => FALSE,
        ],
      ],
    ]);
  }

  protected function installDisplays(): void {
    $display = EntityFormDisplay::load('node.config_service.default');
    if (!$display) {
      $display = EntityFormDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'config_service',
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    $components = [
      'title' => 'string_textfield',
      'langcode' => 'language_select',
      'field_link' => 'string_textfield',
      'field_video' => 'file_generic',
      'field_intro_title' => 'string_textfield',
      'field_intro_lead' => 'text_textarea',
      'field_so_lieu' => 'paragraphs',
      'field_milestones_title' => 'string_textfield',
      'field_cot_moc' => 'paragraphs',
      'field_vision_title' => 'string_textfield',
      'field_tam_nhin' => 'paragraphs',
      'field_leadership_title' => 'string_textfield',
      'field_lanh_dao' => 'paragraphs',
      'field_members_title' => 'string_textfield',
      'field_thanh_vien' => 'paragraphs',
      'field_services_title' => 'string_textfield',
      'field_hub_ring_outer' => 'image_image',
      'field_hub_ring_inner' => 'image_image',
      'field_hub_center' => 'image_image',
      'field_dich_vu' => 'entity_reference_autocomplete',
      'field_projects_title' => 'string_textfield',
      'field_du_an' => 'entity_reference_autocomplete',
      'field_contact_image' => 'image_image',
      'status' => 'boolean_checkbox',
    ];

    $weight = 0;
    foreach ($components as $name => $type) {
      $settings = [];
      if ($type === 'text_textarea') {
        $settings['rows'] = 5;
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

    if (!EntityViewDisplay::load('node.config_service.default')) {
      EntityViewDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'config_service',
        'mode' => 'default',
        'status' => TRUE,
      ])->save();
    }

    $paragraph_displays = [
      'cot_moc_dich_vu' => [
        'field_period' => 'string_textfield',
        'field_body' => 'text_textarea',
        'field_noi_bat' => 'boolean_checkbox',
        'field_key' => 'string_textfield',
        'field_muc_cot_moc' => 'paragraphs',
      ],
      'muc_cot_moc' => [
        'field_title' => 'string_textfield',
        'field_body' => 'text_textarea',
        'field_anh' => 'image_image',
      ],
      'the_tam_nhin' => [
        'field_anh' => 'image_image',
        'field_content' => 'string_textfield',
        'field_title' => 'string_textfield',
        'field_body' => 'text_textarea',
      ],
      'lanh_dao' => [
        'field_anh' => 'image_image',
        'field_hinh_anh' => 'image_image',
        'field_unit' => 'string_textfield',
        'field_title' => 'string_textfield',
        'field_content' => 'string_textfield',
        'field_body' => 'text_textarea',
        'field_noi_bat' => 'boolean_checkbox',
      ],
      'cong_ty_thanh_vien' => [
        'field_anh' => 'image_image',
        'field_title' => 'string_textfield',
        'field_key' => 'string_textfield',
      ],
      'so_lieu_thong_ke' => [
        'field_anh' => 'image_image',
        'field_title' => 'string_textfield',
        'field_unit' => 'string_textfield',
        'field_content' => 'string_textfield',
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
        $settings = $type === 'text_textarea' ? ['rows' => 4] : [];
        if ($type === 'paragraphs') {
          $settings = [
            'title' => 'Mốc',
            'title_plural' => 'Mốc',
            'edit_mode' => 'open',
            'closed_mode' => 'summary',
            'autocollapse' => 'none',
            'add_mode' => 'dropdown',
            'form_display_mode' => 'default',
          ];
        }
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

    if (($info['type'] ?? '') === 'file') {
      $values['settings'] = self::VIDEO_FIELD_SETTINGS;
    }

    if (!empty($info['settings'])) {
      $values['settings'] = array_merge($values['settings'] ?? [], $info['settings']);
    }

    FieldConfig::create($values)->save();
  }

}
