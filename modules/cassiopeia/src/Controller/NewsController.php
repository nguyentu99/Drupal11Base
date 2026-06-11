<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * All-news listing page.
 */
class NewsController extends ControllerBase {

  /**
   * Builds the news listing page.
   */
  public function content(): array {
    return [
      '#theme' => 'cassiopeia_news',
      '#news_page' => cassiopeia_news_page_variables(),
      '#cache' => [
        'contexts' => [
          'url.query_args:q',
          'url.query_args:page',
          'languages:language_interface',
          'languages:language_content',
        ],
        'tags' => ['node_list:article', 'node_list:banner', 'taxonomy_term_list:tx_article'],
      ],
    ];
  }

  /**
   * Page title callback.
   */
  public function title(): string {
    return (string) $this->t('News');
  }

}
