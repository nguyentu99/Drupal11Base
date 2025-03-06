<?php

namespace Drupal\cassiopeia_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

class CassiopeiaAdminAdministratorBlockEditController extends ControllerBase {

  public function content($administrator_block) {
    $block = administrator_block_load($administrator_block);
    $cassiopeiaAdminAdministratorBlockEditForm = \Drupal::formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlockEditForm', $block);
    $build = [
      '#theme' => 'cassiopeia_admin_administrator_block_edit_page',
      '#cassiopeiaAdminAdministratorBlockEditForm' => $cassiopeiaAdminAdministratorBlockEditForm,
    ];
    return $build;
  }

}
