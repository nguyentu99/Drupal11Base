<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Market research listing page.
 */
class MarketResearchController extends ControllerBase {

  /**
   * Builds the market research listing page.
   */
  public function content(): array {
    return [
      '#theme' => 'cassiopeia_market_research',
      '#market_page' => cassiopeia_market_research_page_variables(),
      '#cache' => [
        'contexts' => [
          'url.query_args:q',
          'url.query_args:page',
          'languages:language_interface',
          'languages:language_content',
        ],
        'tags' => ['node_list:market_research', 'node_list:banner'],
      ],
    ];
  }

  /**
   * Page title callback.
   */
  public function title(): string {
    return (string) $this->t('Market research');
  }

}
