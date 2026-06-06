<?php

namespace Drupal\cassiopeia_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Administrator block management pages.
 */
class CassiopeiaAdminAdministratorController extends ControllerBase {

  /**
   * Block list and add form page.
   */
  public function content(): array {
    return [
      '#theme' => 'cassiopeia_admin_administrator_blocks_page',
      '#cassiopeiaAdminAdministratorBlockAddForm' => $this->formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlockAddForm'),
      '#cassiopeiaAdminAdministratorBlocksForm' => $this->formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlocksForm'),
    ];
  }

}
