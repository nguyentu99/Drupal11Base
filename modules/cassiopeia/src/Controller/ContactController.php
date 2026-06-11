<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Contact page.
 */
class ContactController extends ControllerBase {

  /**
   * Builds the contact page.
   */
  public function content(): array {
    return [
      '#theme' => 'cassiopeia_contact',
      '#contact_page' => cassiopeia_contact_page_variables(),
      '#cache' => [
        'tags' => ['config:cassiopeia.settings'],
        'contexts' => ['languages:language_interface'],
      ],
    ];
  }

  /**
   * Page title callback.
   */
  public function title(): string {
    $config = cassiopeia_site_config('contact_page');
    return $config['hero_title'] ?: (string) $this->t('Contact');
  }

}
