<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Investment handbook listing page.
 */
class InvestmentHandbookController extends ControllerBase {

  /**
   * Builds the investment handbook listing page.
   */
  public function content(): array {
    return [
      '#theme' => 'cassiopeia_investment_handbook',
      '#handbook_page' => cassiopeia_investment_handbook_page_variables(),
      '#cache' => [
        'contexts' => [
          'url.query_args:q',
          'languages:language_interface',
          'languages:language_content',
        ],
        'tags' => ['node_list:investment_handbook'],
      ],
    ];
  }

  /**
   * Page title callback.
   */
  public function title(): string {
    return (string) $this->t('Investment handbook');
  }

}
