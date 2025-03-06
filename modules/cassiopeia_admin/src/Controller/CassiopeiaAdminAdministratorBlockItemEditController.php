<?php

namespace Drupal\cassiopeia_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

class CassiopeiaAdminAdministratorBlockItemEditController extends ControllerBase {

  public function content($administrator_block, $administrator_block_item) {
    $block = administrator_block_load($administrator_block);
    $item = administrator_block_item_load($administrator_block_item);
    $cassiopeiaAdminAdministratorBlockItemEditForm = \Drupal::formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlockItemEditForm', $block, $item);
    $build = [
      '#theme' => 'cassiopeia_admin_administrator_block_item_edit_page',
      '#cassiopeiaAdminAdministratorBlockItemEditForm' => $cassiopeiaAdminAdministratorBlockItemEditForm,
    ];
    return $build;
  }

}
