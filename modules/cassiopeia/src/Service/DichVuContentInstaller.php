<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Installs the Dịch vụ (dich_vu) content type, fields, and paragraphs.
 */
class DichVuContentInstaller {

  /**
   * Installs or updates service detail content structures.
   */
  public function install(): void {
    $this->installParagraphTypes();
    $this->installFieldStorages();
    $this->installParagraphFields();
    $this->installNodeType();
    $this->installNodeFields();
    $this->installDisplays();
  }

  /**
   * Creates paragraph bundles used on service detail pages.
   */
  protected function installParagraphTypes(): void {
    $types = [
      'buoc_quy_trinh' => 'Bước quy trình',
      'muc_noi_bat' => 'Mục nổi bật',
      'khoi_noi_bat' => 'Khối nổi bật',
      'tab_hinh_anh' => 'Tab hình ảnh',
      'tai_sao_chon' => 'Tại sao chọn',
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

    if (!ParagraphsType::load('linh_vuc_hoat_dong')) {
      ParagraphsType::create([
        'id' => 'linh_vuc_hoat_dong',
        'label' => 'Lĩnh vực hoạt động',
      ])->save();
    }

    if (!ParagraphsType::load('so_lieu_thong_ke')) {
      ParagraphsType::create([
        'id' => 'so_lieu_thong_ke',
        'label' => 'Số liệu thông kê',
      ])->save();
    }
  }

  /**
   * Creates shared field storage definitions.
   */
  protected function installFieldStorages(): void {
    $storages = [
      'node' => [
        'field_intro_image' => [
          'type' => 'image',
          'cardinality' => 1,
          'settings' => ['uri_scheme' => 'public'],
        ],
        'field_why_image' => [
          'type' => 'image',
          'cardinality' => 1,
          'settings' => ['uri_scheme' => 'public'],
        ],
        'field_so_lieu' => [
          'type' => 'entity_reference_revisions',
          'cardinality' => -1,
          'settings' => ['target_type' => 'paragraph'],
        ],
        'field_khoi_noi_bat' => [
          'type' => 'entity_reference_revisions',
          'cardinality' => -1,
          'settings' => ['target_type' => 'paragraph'],
        ],
        'field_giai_phap' => [
          'type' => 'entity_reference_revisions',
          'cardinality' => -1,
          'settings' => ['target_type' => 'paragraph'],
        ],
        'field_quy_trinh' => [
          'type' => 'entity_reference_revisions',
          'cardinality' => -1,
          'settings' => ['target_type' => 'paragraph'],
        ],
        'field_hinh_anh_tabs' => [
          'type' => 'entity_reference_revisions',
          'cardinality' => -1,
          'settings' => ['target_type' => 'paragraph'],
        ],
        'field_du_an' => [
          'type' => 'entity_reference',
          'cardinality' => -1,
          'settings' => ['target_type' => 'node'],
        ],
        'field_tai_sao' => [
          'type' => 'entity_reference_revisions',
          'cardinality' => -1,
          'settings' => ['target_type' => 'paragraph'],
        ],
      ],
      'paragraph' => [
        'field_hinh_anh' => [
          'type' => 'image',
          'cardinality' => -1,
          'settings' => ['uri_scheme' => 'public'],
        ],
      ],
    ];

    foreach ($storages as $entity_type => $fields) {
      foreach ($fields as $field_name => $definition) {
        if (FieldStorageConfig::loadByName($entity_type, $field_name)) {
          continue;
        }
        FieldStorageConfig::create([
          'field_name' => $field_name,
          'entity_type' => $entity_type,
          'type' => $definition['type'],
          'cardinality' => $definition['cardinality'],
          'settings' => $definition['settings'],
          'translatable' => TRUE,
        ])->save();
      }
    }
  }

  /**
   * Attaches fields to paragraph bundles.
   */
  protected function installParagraphFields(): void {
    $definitions = [
      'so_lieu_thong_ke' => [
        'field_title' => ['label' => 'Giá trị', 'type' => 'string'],
        'field_unit' => ['label' => 'Đơn vị', 'type' => 'string'],
        'field_content' => ['label' => 'Nhãn', 'type' => 'string'],
      ],
      'linh_vuc_hoat_dong' => [
        'field_anh' => ['label' => 'Hình ảnh', 'type' => 'image'],
        'field_title' => ['label' => 'Tiêu đề', 'type' => 'string'],
        'field_body' => ['label' => 'Mô tả', 'type' => 'text_long'],
      ],
      'buoc_quy_trinh' => [
        'field_anh' => ['label' => 'Icon', 'type' => 'image'],
        'field_title' => ['label' => 'Tiêu đề', 'type' => 'string'],
        'field_body' => ['label' => 'Mô tả', 'type' => 'text_long'],
      ],
      'muc_noi_bat' => [
        'field_anh' => ['label' => 'Icon', 'type' => 'image'],
        'field_title' => ['label' => 'Tiêu đề', 'type' => 'string'],
        'field_body' => ['label' => 'Nội dung', 'type' => 'text_long'],
      ],
      'khoi_noi_bat' => [
        'field_anh' => ['label' => 'Hình ảnh', 'type' => 'image'],
        'field_title' => ['label' => 'Tiêu đề', 'type' => 'string'],
        'field_body' => ['label' => 'Mô tả ngắn', 'type' => 'text_long'],
        'field_para_para_content' => [
          'label' => 'Danh sách mục',
          'type' => 'entity_reference_revisions',
          'settings' => [
            'handler' => 'default:paragraph',
            'handler_settings' => [
              'target_bundles' => ['muc_noi_bat' => 'muc_noi_bat'],
              'negate' => 0,
            ],
          ],
        ],
      ],
      'tab_hinh_anh' => [
        'field_title' => ['label' => 'Tên tab', 'type' => 'string'],
        'field_hinh_anh' => ['label' => 'Hình ảnh', 'type' => 'image'],
      ],
      'tai_sao_chon' => [
        'field_content' => ['label' => 'Số thứ tự', 'type' => 'string'],
        'field_title' => ['label' => 'Tiêu đề', 'type' => 'string'],
        'field_body' => ['label' => 'Mô tả', 'type' => 'text_long'],
      ],
    ];

    foreach ($definitions as $bundle => $fields) {
      foreach ($fields as $field_name => $info) {
        $this->ensureField('paragraph', $bundle, $field_name, $info);
      }
    }
  }

  /**
   * Creates the dich_vu node type.
   */
  protected function installNodeType(): void {
    if (NodeType::load('dich_vu')) {
      return;
    }

    NodeType::create([
      'type' => 'dich_vu',
      'name' => 'Dịch vụ',
      'description' => 'Trang chi tiết dịch vụ CNC Industrial.',
      'new_revision' => TRUE,
      'preview_mode' => DRUPAL_OPTIONAL,
      'display_submitted' => FALSE,
    ])->save();
  }

  /**
   * Attaches fields to the dich_vu node bundle.
   */
  protected function installNodeFields(): void {
    $this->ensureField('node', 'dich_vu', 'body', [
      'label' => 'Giới thiệu',
      'type' => 'text_with_summary',
      'settings' => [
        'display_summary' => FALSE,
      ],
    ]);

    $simple_fields = [
      'field_image' => ['label' => 'Ảnh banner', 'type' => 'image'],
      'field_intro_image' => ['label' => 'Ảnh giới thiệu', 'type' => 'image'],
      'field_why_image' => ['label' => 'Ảnh "Tại sao chọn"', 'type' => 'image'],
      'field_anh' => ['label' => 'Logo khách hàng', 'type' => 'image'],
    ];

    foreach ($simple_fields as $field_name => $info) {
      $this->ensureField('node', 'dich_vu', $field_name, $info);
    }

    $paragraph_fields = [
      'field_so_lieu' => [
        'label' => 'Số liệu thống kê',
        'type' => 'entity_reference_revisions',
        'settings' => [
          'handler' => 'default:paragraph',
          'handler_settings' => [
            'target_bundles' => ['so_lieu_thong_ke' => 'so_lieu_thong_ke'],
            'negate' => 0,
          ],
        ],
      ],
      'field_khoi_noi_bat' => [
        'label' => 'Khối nổi bật',
        'type' => 'entity_reference_revisions',
        'settings' => [
          'handler' => 'default:paragraph',
          'handler_settings' => [
            'target_bundles' => ['khoi_noi_bat' => 'khoi_noi_bat'],
            'negate' => 0,
          ],
        ],
      ],
      'field_giai_phap' => [
        'label' => 'Giải pháp / Lĩnh vực',
        'type' => 'entity_reference_revisions',
        'settings' => [
          'handler' => 'default:paragraph',
          'handler_settings' => [
            'target_bundles' => [
              'linh_vuc_hoat_dong' => 'linh_vuc_hoat_dong',
              'co_so_ha_tang' => 'co_so_ha_tang',
            ],
            'negate' => 0,
          ],
        ],
      ],
      'field_quy_trinh' => [
        'label' => 'Quy trình',
        'type' => 'entity_reference_revisions',
        'settings' => [
          'handler' => 'default:paragraph',
          'handler_settings' => [
            'target_bundles' => ['buoc_quy_trinh' => 'buoc_quy_trinh'],
            'negate' => 0,
          ],
        ],
      ],
      'field_hinh_anh_tabs' => [
        'label' => 'Hình ảnh (tab)',
        'type' => 'entity_reference_revisions',
        'settings' => [
          'handler' => 'default:paragraph',
          'handler_settings' => [
            'target_bundles' => ['tab_hinh_anh' => 'tab_hinh_anh'],
            'negate' => 0,
          ],
        ],
      ],
      'field_tai_sao' => [
        'label' => 'Tại sao chọn CNC Industrial',
        'type' => 'entity_reference_revisions',
        'settings' => [
          'handler' => 'default:paragraph',
          'handler_settings' => [
            'target_bundles' => ['tai_sao_chon' => 'tai_sao_chon'],
            'negate' => 0,
          ],
        ],
      ],
      'field_du_an' => [
        'label' => 'Dự án tiêu biểu',
        'type' => 'entity_reference',
        'settings' => [
          'handler' => 'default:node',
          'handler_settings' => [
            'target_bundles' => ['projects' => 'projects'],
            'sort' => ['field' => '_none'],
            'auto_create' => FALSE,
          ],
        ],
      ],
    ];

    foreach ($paragraph_fields as $field_name => $info) {
      $this->ensureField('node', 'dich_vu', $field_name, $info);
    }
  }

  /**
   * Creates form and view displays for dich_vu and new paragraphs.
   */
  protected function installDisplays(): void {
    $this->installNodeFormDisplay();
    $this->installNodeViewDisplays();
    $this->installParagraphFormDisplay('so_lieu_thong_ke', [
      'field_title' => ['type' => 'string_textfield', 'weight' => 0],
      'field_unit' => ['type' => 'string_textfield', 'weight' => 1],
      'field_content' => ['type' => 'string_textfield', 'weight' => 2],
    ]);
    $this->installParagraphFormDisplay('linh_vuc_hoat_dong', [
      'field_title' => ['type' => 'string_textfield', 'weight' => 0],
      'field_body' => ['type' => 'text_textarea', 'weight' => 1],
      'field_anh' => ['type' => 'image_image', 'weight' => 2],
    ]);
    $this->installParagraphFormDisplay('buoc_quy_trinh', [
      'field_title' => ['type' => 'string_textfield', 'weight' => 0],
      'field_body' => ['type' => 'text_textarea', 'weight' => 1],
      'field_anh' => ['type' => 'image_image', 'weight' => 2],
    ]);
    $this->installParagraphFormDisplay('muc_noi_bat', [
      'field_title' => ['type' => 'string_textfield', 'weight' => 0],
      'field_body' => ['type' => 'text_textarea', 'weight' => 1],
      'field_anh' => ['type' => 'image_image', 'weight' => 2],
    ]);
    $this->installParagraphFormDisplay('khoi_noi_bat', [
      'field_title' => ['type' => 'string_textfield', 'weight' => 0],
      'field_body' => ['type' => 'text_textarea', 'weight' => 1],
      'field_anh' => ['type' => 'image_image', 'weight' => 2],
      'field_para_para_content' => ['type' => 'paragraphs', 'weight' => 3],
    ]);
    $this->installParagraphFormDisplay('tab_hinh_anh', [
      'field_title' => ['type' => 'string_textfield', 'weight' => 0],
      'field_hinh_anh' => ['type' => 'image_image', 'weight' => 1],
    ]);
    $this->installParagraphFormDisplay('tai_sao_chon', [
      'field_content' => ['type' => 'string_textfield', 'weight' => 0],
      'field_title' => ['type' => 'string_textfield', 'weight' => 1],
      'field_body' => ['type' => 'text_textarea', 'weight' => 2],
    ]);
  }

  /**
   * Builds the node edit form for dich_vu.
   */
  protected function installNodeFormDisplay(): void {
    $display = EntityFormDisplay::load('node.dich_vu.default');
    if (!$display) {
      $display = EntityFormDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'dich_vu',
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    $components = [
      'title' => ['type' => 'string_textfield', 'weight' => 0],
      'langcode' => ['type' => 'language_select', 'weight' => 1],
      'field_image' => ['type' => 'image_image', 'weight' => 2],
      'body' => ['type' => 'text_textarea_with_summary', 'weight' => 3],
      'field_intro_image' => ['type' => 'image_image', 'weight' => 4],
      'field_so_lieu' => ['type' => 'paragraphs', 'weight' => 5],
      'field_khoi_noi_bat' => ['type' => 'paragraphs', 'weight' => 6],
      'field_giai_phap' => ['type' => 'paragraphs', 'weight' => 7],
      'field_quy_trinh' => ['type' => 'paragraphs', 'weight' => 8],
      'field_hinh_anh_tabs' => ['type' => 'paragraphs', 'weight' => 9],
      'field_anh' => ['type' => 'image_image', 'weight' => 10],
      'field_du_an' => ['type' => 'entity_reference_autocomplete', 'weight' => 11],
      'field_tai_sao' => ['type' => 'paragraphs', 'weight' => 12],
      'field_why_image' => ['type' => 'image_image', 'weight' => 13],
      'path' => ['type' => 'path', 'weight' => 20],
      'status' => ['type' => 'boolean_checkbox', 'weight' => 21],
    ];

    foreach ($components as $name => $component) {
      $display->setComponent($name, $component + ['region' => 'content']);
    }

    $display->save();
  }

  /**
   * Builds default and teaser view modes for dich_vu nodes.
   */
  protected function installNodeViewDisplays(): void {
    foreach (['default', 'teaser'] as $mode) {
      $display = EntityViewDisplay::load("node.dich_vu.$mode");
      if (!$display) {
        $display = EntityViewDisplay::create([
          'targetEntityType' => 'node',
          'bundle' => 'dich_vu',
          'mode' => $mode,
          'status' => TRUE,
        ]);
      }

      if ($mode === 'teaser') {
        $display->setComponent('title', [
          'type' => 'string',
          'weight' => 0,
          'label' => 'hidden',
          'region' => 'content',
        ]);
        $display->setComponent('body', [
          'type' => 'text_summary_or_trimmed',
          'weight' => 1,
          'label' => 'hidden',
          'region' => 'content',
          'settings' => ['trim_length' => 300],
        ]);
        $display->save();
        continue;
      }

      $components = [
        'body' => ['type' => 'text_default', 'weight' => 0],
        'field_image' => ['type' => 'image', 'weight' => 1],
        'field_intro_image' => ['type' => 'image', 'weight' => 2],
        'field_so_lieu' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 3],
        'field_khoi_noi_bat' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 4],
        'field_giai_phap' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 5],
        'field_quy_trinh' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 6],
        'field_hinh_anh_tabs' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 7],
        'field_anh' => ['type' => 'image', 'weight' => 8],
        'field_du_an' => ['type' => 'entity_reference_entity_view', 'weight' => 9],
        'field_tai_sao' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 10],
        'field_why_image' => ['type' => 'image', 'weight' => 11],
      ];

      foreach ($components as $field_name => $component) {
        $display->setComponent($field_name, $component + [
          'label' => 'above',
          'region' => 'content',
        ]);
      }

      $display->save();
    }
  }

  /**
   * Builds a paragraph form display.
   *
   * @param string $bundle
   *   Paragraph bundle machine name.
   * @param array<string, array<string, mixed>> $components
   *   Form display components keyed by field name.
   */
  protected function installParagraphFormDisplay(string $bundle, array $components): void {
    $display = EntityFormDisplay::load("paragraph.$bundle.default");
    if (!$display) {
      $display = EntityFormDisplay::create([
        'targetEntityType' => 'paragraph',
        'bundle' => $bundle,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    foreach ($components as $name => $component) {
      $settings = [];
      if ($component['type'] === 'text_textarea') {
        $settings['rows'] = 5;
      }
      if ($component['type'] === 'paragraphs') {
        $settings = [
          'title' => 'Đoạn',
          'title_plural' => 'Đoạn',
          'edit_mode' => 'open',
          'closed_mode' => 'summary',
          'autocollapse' => 'none',
          'closed_mode_threshold' => 0,
          'add_mode' => 'dropdown',
          'form_display_mode' => 'default',
          'default_paragraph_type' => 'muc_noi_bat',
        ];
      }

      $display->setComponent($name, [
        'type' => $component['type'],
        'weight' => $component['weight'],
        'region' => 'content',
        'settings' => $settings,
      ]);
    }

    $display->save();
  }

  /**
   * Ensures a field instance exists on a bundle.
   *
   * @param string $entity_type
   *   Entity type ID.
   * @param string $bundle
   *   Bundle machine name.
   * @param string $field_name
   *   Field machine name.
   * @param array<string, mixed> $info
   *   Field definition metadata.
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

    if (!empty($info['settings'])) {
      $values['settings'] = $info['settings'];
    }

    FieldConfig::create($values)->save();
  }

}
