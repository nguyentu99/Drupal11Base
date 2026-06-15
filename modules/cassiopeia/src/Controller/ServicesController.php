<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Services landing page (/dich-vu).
 */
class ServicesController extends ControllerBase {

  /**
   * Builds the services landing page.
   */
  public function content(): array {
    /** @var \Drupal\cassiopeia\Service\ConfigServiceContentProvider $provider */
    $provider = \Drupal::service('cassiopeia.config_service_content_provider');
    $page = $provider->build();

    return [
      '#theme' => 'cassiopeia_services_page',
      '#services_page' => $page,
      '#contact_form' => $this->formBuilder()->getForm('Drupal\cassiopeia\Form\ContactForm'),
      '#cache' => [
        'tags' => $page['cache_tags'] ?? ['node_list:config_service'],
        'contexts' => ['languages:language_content', 'session'],
      ],
      '#attached' => [
        'library' => [
          'cassiopeia/contact-form',
        ],
      ],
    ];
  }

  /**
   * Page title callback.
   */
  public function title(): string {
    $page = \Drupal::service('cassiopeia.config_service_content_provider')->build();
    $title = (string) ($page['hero']['title'] ?? '');
    return $title !== '' ? $title : (string) $this->t('Industrial solutions');
  }

}
