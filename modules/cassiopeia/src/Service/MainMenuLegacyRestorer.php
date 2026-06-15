<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Service;

use Drupal\menu_link_content\MenuLinkContentInterface;

/**
 * Restores the original main menu links (ids 1–29) after structure sync.
 */
class MainMenuLegacyRestorer {

  private const MENU_NAME = 'main';

  private const MAX_LEGACY_ID = 29;

  /**
   * Original main menu snapshot (before HTML structure sync).
   *
   * @var array<int, array{title: string, parent_id: int, weight: int, uri: string}>
   */
  private const LEGACY_LINKS = [
    1 => ['title' => 'Về chúng tôi', 'parent_id' => 0, 'weight' => -49, 'uri' => 'route:<nolink>'],
    2 => ['title' => 'Thông điệp chủ tịch HĐQT', 'parent_id' => 1, 'weight' => -50, 'uri' => 'https://cnctech.com.vn/vi/thong-diep-chu-tich-hdqt'],
    3 => ['title' => 'Dịch vụ', 'parent_id' => 0, 'weight' => -48, 'uri' => 'internal:/dich-vu'],
    4 => ['title' => 'Dự án', 'parent_id' => 0, 'weight' => -47, 'uri' => 'route:<nolink>'],
    5 => ['title' => 'Tài nguyên', 'parent_id' => 0, 'weight' => -43, 'uri' => 'route:<nolink>'],
    6 => ['title' => 'Cẩm nang đầu tư', 'parent_id' => 5, 'weight' => -50, 'uri' => 'internal:/cam-nang-dau-tu'],
    7 => ['title' => 'Nghiên cứu thị trường', 'parent_id' => 5, 'weight' => -49, 'uri' => 'internal:/nghien-cuu-thi-truong'],
    8 => ['title' => 'Thư viện hình ảnh', 'parent_id' => 5, 'weight' => -48, 'uri' => 'internal:/thu-vien-anh'],
    9 => ['title' => 'Cơ hội nghề nghiệp', 'parent_id' => 0, 'weight' => -44, 'uri' => 'https://cnctech.com.vn/vi/career'],
    10 => ['title' => 'HAPPY CNCERS', 'parent_id' => 0, 'weight' => -45, 'uri' => 'https://cnctech.com.vn/vi/happy-cncers'],
    11 => ['title' => 'Tin tức', 'parent_id' => 0, 'weight' => -46, 'uri' => 'internal:/news'],
    12 => ['title' => 'Sự kiện', 'parent_id' => 11, 'weight' => 0, 'uri' => 'internal:/taxonomy/term/3'],
    13 => ['title' => 'Tin dự án', 'parent_id' => 11, 'weight' => 0, 'uri' => 'internal:/taxonomy/term/2'],
    14 => ['title' => 'Tin tức doanh nghiệp', 'parent_id' => 11, 'weight' => 0, 'uri' => 'internal:/taxonomy/term/1'],
    15 => ['title' => 'Tin tức sự kiện', 'parent_id' => 11, 'weight' => 0, 'uri' => 'internal:/taxonomy/term/4'],
    16 => ['title' => 'Tầm nhìn - Sứ mệnh', 'parent_id' => 1, 'weight' => -49, 'uri' => 'https://cnctech.com.vn/vi/tam-nhin-su-menh'],
    17 => ['title' => 'Lịch sử', 'parent_id' => 1, 'weight' => -48, 'uri' => 'https://cnctech.com.vn/vi/lich-su-va-thanh-tuu'],
    18 => ['title' => 'Văn hóa doanh nghiệp', 'parent_id' => 1, 'weight' => -47, 'uri' => 'https://cnctech.com.vn/vi/van-hoa-doanh-nghiep'],
    19 => ['title' => 'Giải thưởng', 'parent_id' => 1, 'weight' => -46, 'uri' => 'https://cnctech.com.vn/vi/giai-thuong'],
    20 => ['title' => 'ESG', 'parent_id' => 1, 'weight' => -45, 'uri' => 'https://cnctech.com.vn/vi/esg'],
    21 => ['title' => 'Đơn vị thành viên', 'parent_id' => 1, 'weight' => -44, 'uri' => 'https://cnctech.com.vn/vi/don-vi-thanh-vien'],
    22 => ['title' => 'Đội ngũ lãnh đạo', 'parent_id' => 1, 'weight' => -43, 'uri' => 'https://cnctech.com.vn/vi/doi-ngu-lanh-dao'],
    23 => ['title' => 'Mạng lưới hoạt động', 'parent_id' => 1, 'weight' => -42, 'uri' => 'internal:/node/6'],
    24 => ['title' => 'Đối tác & khách hàng', 'parent_id' => 1, 'weight' => 0, 'uri' => 'internal:/node/7'],
    25 => ['title' => 'Khu công nghiệp Nam Bình Xuyên Green Park', 'parent_id' => 4, 'weight' => 0, 'uri' => 'entity:node/8'],
    26 => ['title' => 'Thiết lập hệ thống kỹ thuật và nội thất nhà xưởng', 'parent_id' => 3, 'weight' => 0, 'uri' => 'entity:node/9'],
    27 => ['title' => 'Quản lý, vận hành khu công nghiệp', 'parent_id' => 3, 'weight' => 0, 'uri' => 'entity:node/10'],
    28 => ['title' => 'Thiết kế thi công giải pháp phòng cháy công nghiệp', 'parent_id' => 3, 'weight' => 0, 'uri' => 'entity:node/11'],
    29 => ['title' => 'Thiết kế và xây dựng công trình công nghiệp', 'parent_id' => 3, 'weight' => 0, 'uri' => 'entity:node/12'],
  ];

  /**
   * @return string[]
   */
  public function restore(): array {
    $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $plugin_ids = [];
    $restored = 0;
    $missing = 0;

    foreach (array_keys(self::LEGACY_LINKS) as $id) {
      $link = $storage->load($id);
      if ($link instanceof MenuLinkContentInterface) {
        $plugin_ids[$id] = $link->getPluginId();
      }
    }

    foreach (self::LEGACY_LINKS as $id => $definition) {
      $link = $storage->load($id);
      if (!$link instanceof MenuLinkContentInterface) {
        $missing++;
        continue;
      }

      $parent_id = $definition['parent_id'];
      $parent_plugin = $parent_id > 0 ? ($plugin_ids[$parent_id] ?? '') : '';

      $link->set('title', $definition['title']);
      $link->link = ['uri' => $definition['uri']];
      $link->set('parent', $parent_plugin);
      $link->set('weight', $definition['weight']);
      $link->set('enabled', TRUE);
      $link->set('expanded', $parent_id === 0);
      $link->save();
      $restored++;
    }

    $disabled = 0;
    foreach ($storage->loadByProperties(['menu_name' => self::MENU_NAME]) as $link) {
      if ((int) $link->id() > self::MAX_LEGACY_ID) {
        $link->set('enabled', FALSE);
        $link->save();
        $disabled++;
      }
    }

    return [(string) t('Restored @restored legacy main menu links, disabled @disabled added links (@missing missing).', [
      '@restored' => $restored,
      '@disabled' => $disabled,
      '@missing' => $missing,
    ])];
  }

}
