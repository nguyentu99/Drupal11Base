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
      '#contact_form' => $this->formBuilder()->getForm('Drupal\cassiopeia\Form\ContactForm'),
      '#cache' => [
        'tags' => ['config:cassiopeia.settings'],
        'contexts' => ['languages:language_interface', 'session'],
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Page title callback.
   */
  public function title(): string {
    $page = cassiopeia_contact_page_variables();
    $title = (string) ($page['hero_title'] ?? '');
    return $title !== '' ? $title : (string) $this->t('Contact');
  }

}
