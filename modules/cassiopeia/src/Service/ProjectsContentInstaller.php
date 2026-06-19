<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\image\Entity\ImageStyle;

/**
 * Installs projects image styles and display settings for cropped images.
 */
class ProjectsContentInstaller {

  public const STYLE_TEASER = 'projects_teaser';

  public const STYLE_HERO = 'projects_hero';

  public const STYLE_SERVICES_SHOWCASE = 'services_showcase';

  public const STYLE_CONTENT_CARD = 'content_card';

  public const STYLE_HANDBOOK_CARD = 'handbook_card';

  public const STYLE_CONTENT_THUMB = 'content_thumb';

  public const STYLE_PAGE_HERO = 'page_hero';

  public const STYLE_TAX = 'projects_629x385';

  /**
   * Installs image styles and display configuration.
   */
  public function install(): void {
    $this->installImageStyles();
    $this->configureDisplays();
    $this->configureDichVuDisplays();
    $this->configureArticleDisplays();
    $this->configureHandbookDisplays();
    $this->configureMarketResearchDisplays();
  }

  /**
   * Creates cropped image styles for project cards and banners.
   */
  public function installImageStyles(): void {
    $this->ensureImageStyle(self::STYLE_TEASER, 'Projects card (420×293)', 420, 293);
    $this->ensureImageStyle(self::STYLE_HERO, 'Projects banner (1920×596)', 1920, 596);
    $this->ensureImageStyle(self::STYLE_SERVICES_SHOWCASE, 'Services showcase (1920×768)', 1920, 768);
    $this->ensureImageStyle(self::STYLE_CONTENT_CARD, 'Content card (420×288)', 420, 288);
    $this->ensureImageStyle(self::STYLE_HANDBOOK_CARD, 'Handbook card (420×312)', 420, 312);
    $this->ensureImageStyle(self::STYLE_CONTENT_THUMB, 'Content thumb (110×78)', 110, 78);
    $this->ensureImageStyle(self::STYLE_PAGE_HERO, 'Page hero (1920×756)', 1920, 756);
    $this->ensureImageStyle(self::STYLE_TAX, 'Projects tax section (629×385)', 629, 385);
  }

  /**
   * Applies image styles to projects form and view displays.
   */
  public function configureDisplays(): void {
    $form = EntityFormDisplay::load('node.projects.default');
    if ($form) {
      if ($component = $form->getComponent('field_image')) {
        $component['settings']['preview_image_style'] = self::STYLE_TEASER;
        $form->setComponent('field_image', $component);
      }
      if ($component = $form->getComponent('field_banner')) {
        $component['settings']['preview_image_style'] = self::STYLE_HERO;
        $form->setComponent('field_banner', $component);
      }
      $form->save();
    }

    $default = EntityViewDisplay::load('node.projects.default');
    if ($default) {
      if ($component = $default->getComponent('field_image')) {
        $component['settings']['image_style'] = self::STYLE_TEASER;
        $default->setComponent('field_image', $component);
      }
      if ($component = $default->getComponent('field_banner')) {
        $component['settings']['image_style'] = self::STYLE_HERO;
        $default->setComponent('field_banner', $component);
      }
      $default->save();
    }

    $teaser = EntityViewDisplay::load('node.projects.teaser');
    if ($teaser && ($component = $teaser->getComponent('field_image'))) {
      $component['settings']['image_style'] = self::STYLE_TEASER;
      $teaser->setComponent('field_image', $component);
      $teaser->save();
    }
  }

  /**
   * Applies cropped card style to article images.
   */
  public function configureArticleDisplays(): void {
    $this->applyFieldImageStyle('article', self::STYLE_CONTENT_CARD);
  }

  /**
   * Applies cropped card style to investment handbook images.
   */
  public function configureHandbookDisplays(): void {
    $this->applyFieldImageStyle('investment_handbook', self::STYLE_HANDBOOK_CARD);
  }

  /**
   * Applies cropped card style to market research images.
   */
  public function configureMarketResearchDisplays(): void {
    $this->applyFieldImageStyle('market_research', self::STYLE_CONTENT_CARD);
  }

  /**
   * Applies cropped showcase style to dich_vu banner field.
   */
  public function configureDichVuDisplays(): void {
    $form = EntityFormDisplay::load('node.dich_vu.default');
    if ($form && ($component = $form->getComponent('field_image'))) {
      $component['settings']['preview_image_style'] = self::STYLE_SERVICES_SHOWCASE;
      $form->setComponent('field_image', $component);
      $form->save();
    }

    foreach (['default', 'teaser'] as $mode) {
      $display = EntityViewDisplay::load('node.dich_vu.' . $mode);
      if (!$display || !($component = $display->getComponent('field_image'))) {
        continue;
      }
      $component['settings']['image_style'] = self::STYLE_SERVICES_SHOWCASE;
      $display->setComponent('field_image', $component);
      $display->save();
    }
  }

  /**
   * Sets preview and view display image styles for a node field_image.
   */
  private function applyFieldImageStyle(string $bundle, string $style, array $view_modes = ['default', 'teaser']): void {
    $form = EntityFormDisplay::load('node.' . $bundle . '.default');
    if ($form && ($component = $form->getComponent('field_image'))) {
      $component['settings']['preview_image_style'] = $style;
      $form->setComponent('field_image', $component);
      $form->save();
    }

    foreach ($view_modes as $mode) {
      $display = EntityViewDisplay::load('node.' . $bundle . '.' . $mode);
      if (!$display || !($component = $display->getComponent('field_image'))) {
        continue;
      }
      $component['settings']['image_style'] = $style;
      $display->setComponent('field_image', $component);
      $display->save();
    }
  }

  private function ensureImageStyle(string $name, string $label, int $width, int $height): void {
    $storage = \Drupal::entityTypeManager()->getStorage('image_style');
    /** @var \Drupal\image\ImageStyleInterface|null $style */
    $style = $storage->load($name);
    if (!$style) {
      $style = ImageStyle::create([
        'name' => $name,
        'label' => $label,
      ]);
    }
    else {
      $style->set('label', $label);
      $style->set('effects', []);
    }

    $style->addImageEffect([
      'id' => 'image_scale_and_crop',
      'data' => [
        'width' => $width,
        'height' => $height,
        'anchor' => 'center-center',
      ],
    ]);

    if (\Drupal::moduleHandler()->moduleExists('image')) {
      $effects = $style->get('effects') ?? [];
      $has_webp = FALSE;
      foreach ($effects as $effect) {
        if (($effect['id'] ?? '') === 'image_convert') {
          $has_webp = TRUE;
          break;
        }
      }
      if (!$has_webp) {
        $style->addImageEffect([
          'id' => 'image_convert',
          'data' => [
            'extension' => 'webp',
          ],
          'weight' => 2,
        ]);
      }
    }

    $style->save();
  }

}
