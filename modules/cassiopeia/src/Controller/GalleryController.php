<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Photo gallery listing page.
 */
class GalleryController extends ControllerBase {

  /**
   * Builds the gallery listing page.
   */
  public function content(): array {
    $theme_path = \Drupal::service('extension.list.theme')->getPath('cassiopeia_theme');

    return [
      '#theme' => 'cassiopeia_gallery',
      '#gallery_page' => cassiopeia_gallery_page_variables(),
      '#attached' => [
        'library' => ['cassiopeia/gallery'],
        'drupalSettings' => [
          'cassiopeiaGallery' => [
            'albumIcon' => '/' . $theme_path . '/images/svg/album-icon.svg',
          ],
        ],
      ],
      '#cache' => [
        'contexts' => [
          'url.query_args:album',
          'url.query_args:page',
          'languages:language_interface',
          'languages:language_content',
        ],
        'tags' => ['node_list:gallery', 'node_list:banner'],
      ],
    ];
  }

  /**
   * Page title callback.
   */
  public function title(): string {
    return (string) $this->t('Photo gallery');
  }

}
