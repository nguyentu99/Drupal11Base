<?php

namespace Drupal\cassiopeia_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

class CassiopeiaAdminAdministratorBlockItemsController extends ControllerBase {

  public function content($administrator_block) {
    $block = administrator_block_load($administrator_block);
    $cassiopeiaAdminAdministratorBlockItemAddForm = \Drupal::formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlockItemAddForm', $block);
    $cassiopeiaAdminAdministratorBlockItemsForm = \Drupal::formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlockItemsForm', $block);
    $build = [
      '#theme' => 'cassiopeia_admin_administrator_block_items_page',
      '#cassiopeiaAdminAdministratorBlockItemAddForm' => $cassiopeiaAdminAdministratorBlockItemAddForm,
      '#cassiopeiaAdminAdministratorBlockItemsForm' => $cassiopeiaAdminAdministratorBlockItemsForm,
    ];
    return $build;

  }
}
