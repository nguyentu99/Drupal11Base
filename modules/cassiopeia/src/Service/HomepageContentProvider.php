<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Language\LanguageInterface;
use Drupal\node\NodeInterface;

/**
 * Builds homepage template variables from admin content.
 *
 * Dynamic lists (dich_vu, projects, article) come from published nodes.
 * Static homepage blocks are edited via the home_config content type.
 */
class HomepageContentProvider {

  /**
   * Theme background images cycled for service slides when nodes have no image.
   *
   * @var list<string>
   */
  private const SERVICE_BG_IMAGES = [
    'services-bg.jpg',
    'service-bg-2.jpg',
    'service-bg-3.jpg',
    'service-bg-4.jpg',
    'service-bg-1-1.jpg',
    'service-bg-2-2.jpg',
    'service-bg-3-3.jpg',
    'service-bg-4-4.jpg',
  ];

  /**
   * Builds homepage variables for the front page template.
   *
   * @return array<string, mixed>
   */
  public function build(?LanguageInterface $language = NULL): array {
    $language ??= \Drupal::languageManager()->getCurrentLanguage();
    $theme_path = '/' . \Drupal::service('extension.list.theme')->getPath('cassiopeia_theme');

    $home_config_node = cassiopeia_home_config_node($language);
    $config = cassiopeia_home_config_data($home_config_node, $theme_path);

    return array_merge($config, [
      'hero' => $this->buildHero($language),
      'services' => $this->buildServices($theme_path, $language),
      'services_default_bg' => $theme_path . '/images/services-bg.jpg',
      'projects' => $this->buildProjects($theme_path, $language),
      'news' => $this->buildNews($theme_path, $language),
    ]);
  }

  /**
   * Hero banner from banner node (home); theme image when no banner exists.
   */
  private function buildHero(LanguageInterface $language): array {
    $default_alt = 'Khu công nghiệp Green Park IP';
    $banner = cassiopeia_page_banner('home', $default_alt, $language);
    return [
      'image' => $banner['hero_image'],
      'alt' => $banner['hero_image_alt'] ?: $default_alt,
    ];
  }

  /**
   * Service showcase from published dich_vu nodes only.
   *
   * @return array<int, array<string, string>>
   */
  private function buildServices(string $theme_path, LanguageInterface $language): array {
    $nids = $this->loadPublishedNodeIds('dich_vu', $language);
    if ($nids === []) {
      return [];
    }

    $items = [];
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
    $index = 0;
    foreach ($nids as $nid) {
      if (!isset($nodes[$nid])) {
        continue;
      }
      $node = $nodes[$nid];
      $bg_file = self::SERVICE_BG_IMAGES[$index % count(self::SERVICE_BG_IMAGES)];
      $items[] = [
        'title_html' => $this->serviceTitleHtml($node->label()),
        'description' => $this->nodeIntroText($node),
        'url' => $node->toUrl('canonical', ['language' => $language])->toString(),
        'bg' => $theme_path . '/images/' . $bg_file,
      ];
      $index++;
    }

    return $items;
  }

  /**
   * Projects featured block and carousel from published projects nodes only.
   *
   * @return array{featured: array<string, mixed>|null, cards: array<int, array<string, string>>}
   */
  private function buildProjects(string $theme_path, LanguageInterface $language): array {
    $nids = $this->loadPublishedNodeIds('projects', $language);
    if ($nids === []) {
      return [
        'featured' => NULL,
        'cards' => [],
      ];
    }

    $location_icon = $theme_path . '/images/svg/location-green.svg';
    $location_icon_white = $theme_path . '/images/svg/location-white.svg';
    $cards = [];

    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
    foreach ($nids as $nid) {
      if (!isset($nodes[$nid])) {
        continue;
      }
      $teaser = cassiopeia_projects_teaser_data($nodes[$nid]);
      $cards[] = [
        'title' => $teaser['title'],
        'url' => $teaser['url'],
        'image' => $teaser['image'],
        'image_alt' => $teaser['image_alt'],
        'address' => $teaser['address'],
        'location_icon' => $location_icon,
      ];
    }

    $featured_node = $nodes[$nids[0]];
    $featured_teaser = cassiopeia_projects_teaser_data($featured_node);
    $featured = [
      'title' => $featured_teaser['title'],
      'title_html' => $this->projectFeaturedTitleHtml($featured_teaser['title']),
      'image' => $featured_teaser['image'],
      'image_alt' => $featured_teaser['image_alt'],
      'address' => $featured_teaser['address'],
      'url' => $featured_teaser['url'],
      'location_icon' => $location_icon_white,
    ];

    return [
      'featured' => $featured,
      'cards' => $cards,
    ];
  }

  /**
   * News carousel from published article nodes only (no static filler cards).
   *
   * @return array<int, array<string, string>>
   */
  private function buildNews(string $theme_path, LanguageInterface $language): array {
    $nids = $this->loadPublishedNodeIds('article', $language, 12);
    if ($nids === []) {
      return [];
    }

    $items = [];
    $date_formatter = \Drupal::service('date.formatter');
    $placeholder_image = $theme_path . '/images/news/news-1.jpg';
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
    foreach ($nids as $nid) {
      if (!isset($nodes[$nid])) {
        continue;
      }
      $data = cassiopeia_article_teaser_data($nodes[$nid], $date_formatter);
      $items[] = [
        'title' => $data['title'],
        'url' => $data['url'],
        'category' => $data['category'] !== '' ? $data['category'] : 'Tin tức Doanh nghiệp',
        'date' => $data['date'],
        'date_iso' => $data['date_iso'],
        'image' => $data['image']['url'] ?? $placeholder_image,
        'image_alt' => $data['image']['alt'] ?? $data['title'],
      ];
    }

    return $items;
  }

  /**
   * @return list<int|string>
   */
  private function loadPublishedNodeIds(string $bundle, LanguageInterface $language, int $limit = 0): array {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', $bundle)
      ->condition('status', 1)
      ->sort('created', 'DESC');

    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      $query->condition('langcode', $language->getId());
    }

    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $nids = $query->execute();
    return $nids === [] ? [] : array_values($nids);
  }

  private function projectFeaturedTitleHtml(string $title): string {
    if (preg_match('/^(.*Nam Bình Xuyên)\s+(Green Park.*)$/iu', $title, $matches)) {
      return $matches[1] . '<br>' . $matches[2];
    }
    return $title;
  }

  private function nodeIntroText(NodeInterface $node): string {
    if (!$node->hasField('body') || $node->get('body')->isEmpty()) {
      return '';
    }
    $paragraphs = cassiopeia_html_paragraph_texts((string) $node->get('body')->processed);
    return $paragraphs[0] ?? '';
  }

  private function serviceTitleHtml(string $title): string {
    if (str_contains($title, '<br')) {
      return $title;
    }
    $parts = preg_split('/\s+(và|&)\s+/u', $title, 2);
    if (is_array($parts) && count($parts) === 2) {
      return $parts[0] . '<br>' . $parts[1];
    }
    return $title;
  }

}
