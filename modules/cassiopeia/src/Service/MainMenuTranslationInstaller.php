<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Service;

use Drupal\menu_link_content\MenuLinkContentInterface;

/**
 * Enables and seeds English translations for the main navigation menu.
 */
class MainMenuTranslationInstaller {

  private const MENU_NAME = 'main';

  private const LANGCODE = 'en';

  /**
   * Vietnamese menu titles mapped to English (trimmed keys).
   *
   * @var array<string, string>
   */
  private const TITLE_MAP = [
    'Về chúng tôi' => 'About us',
    'Thông điệp chủ tịch HĐQT' => 'Message from the Chairman',
    'Dịch vụ' => 'Services',
    'Dự án' => 'Projects',
    'Tài nguyên' => 'Resources',
    'Cẩm nang đầu tư' => 'Investment handbook',
    'Nghiên cứu thị trường' => 'Market research',
    'Thư viện hình ảnh' => 'Photo gallery',
    'Tin tức' => 'News',
    'HAPPY CNCERS' => 'HAPPY CNCERS',
    'Cơ hội nghề nghiệp' => 'Careers',
    'Sự kiện' => 'Events',
    'Tin dự án' => 'Project news',
    'Tin tức doanh nghiệp' => 'Corporate news',
    'Tin tức sự kiện' => 'Event news',
    'Tầm nhìn - Sứ mệnh' => 'Vision - Mission',
    'Lịch sử' => 'History',
    'Văn hóa doanh nghiệp' => 'Corporate culture',
    'Giải thưởng' => 'Awards',
    'ESG' => 'ESG',
    'Đơn vị thành viên' => 'Member companies',
    'Đội ngũ lãnh đạo' => 'Leadership team',
    'Mạng lưới hoạt động' => 'Active network',
    'Đối tác & khách hàng' => 'Partners & customers',
    'Khu công nghiệp Nam Bình Xuyên Green Park' => 'Nam Binh Xuyen Green Park Industrial Park',
    'Thiết lập hệ thống kỹ thuật và nội thất nhà xưởng' => 'Factory technical systems and interior fit-out',
    'Quản lý, vận hành khu công nghiệp' => 'Industrial park management and operations',
    'Thiết kế thi công giải pháp phòng cháy công nghiệp' => 'Industrial fire protection design and construction',
    'Thiết kế và xây dựng công trình công nghiệp' => 'Industrial facility design and construction',
  ];

  /**
   * Enables content translation for custom menu links.
   */
  public function enableContentTranslation(): void {
    if (!\Drupal::moduleHandler()->moduleExists('content_translation')) {
      return;
    }

    /** @var \Drupal\content_translation\ContentTranslationManagerInterface $manager */
    $manager = \Drupal::service('content_translation.manager');
    if (!$manager->isSupported('menu_link_content')) {
      return;
    }

    $manager->setEnabled('menu_link_content', 'menu_link_content', TRUE);
    $manager->setBundleTranslationSettings('menu_link_content', 'menu_link_content', [
      'untranslatable_fields_hide' => '0',
    ]);
  }

  /**
   * Adds or updates English translations for all main menu links.
   *
   * @return string[]
   */
  public function importEnglishTranslations(): array {
    $this->enableContentTranslation();

    $messages = [];
    $created = 0;
    $updated = 0;
    $skipped = 0;

    $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $links = $storage->loadByProperties(['menu_name' => self::MENU_NAME]);

    foreach ($links as $link) {
      if (!$link instanceof MenuLinkContentInterface) {
        continue;
      }

      $vi_title = trim($link->label());
      $en_title = self::TITLE_MAP[$vi_title] ?? NULL;
      if ($en_title === NULL) {
        $messages[] = (string) t('Skipped menu link @id — no English title mapping for "@title".', [
          '@id' => $link->id(),
          '@title' => $vi_title,
        ]);
        $skipped++;
        continue;
      }

      if ($link->hasTranslation(self::LANGCODE)) {
        $translation = $link->getTranslation(self::LANGCODE);
        if (trim($translation->label()) === $en_title) {
          $skipped++;
          continue;
        }
        $translation->set('title', $en_title);
        $this->localizeLinkField($translation);
        $translation->save();
        $updated++;
        continue;
      }

      $values = [];
      foreach ($link->getTranslatableFields() as $field_name => $field) {
        $values[$field_name] = $link->get($field_name)->getValue();
      }
      $values['title'] = $en_title;
      $values['link'] = $this->localizeLinkValues($values['link'] ?? []);

      $link->addTranslation(self::LANGCODE, $values);
      $link->save();
      $created++;
    }

    $messages[] = (string) t('Main menu English translations: @created created, @updated updated, @skipped unchanged or skipped.', [
      '@created' => $created,
      '@updated' => $updated,
      '@skipped' => $skipped,
    ]);

    return $messages;
  }

  /**
   * Localizes external URLs on an existing menu link translation.
   */
  private function localizeLinkField(MenuLinkContentInterface $link): void {
    $values = $link->get('link')->getValue();
    $localized = $this->localizeLinkValues($values);
    if ($localized !== $values) {
      $link->set('link', $localized);
    }
  }

  /**
   * Swaps /vi/ for /en/ on external CNC Tech URLs.
   *
   * @param array<int, array<string, mixed>> $values
   *
   * @return array<int, array<string, mixed>>
   */
  private function localizeLinkValues(array $values): array {
    if ($values === [] || empty($values[0]['uri'])) {
      return $values;
    }

    $uri = (string) $values[0]['uri'];
    if (str_contains($uri, 'cnctech.com.vn/vi/')) {
      $values[0]['uri'] = str_replace('cnctech.com.vn/vi/', 'cnctech.com.vn/en/', $uri);
    }

    return $values;
  }

}
