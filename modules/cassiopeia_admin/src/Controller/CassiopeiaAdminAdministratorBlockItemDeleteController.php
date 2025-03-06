<?php

namespace Drupal\cassiopeia_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

class CassiopeiaAdminAdministratorBlockItemDeleteController extends ControllerBase {

  public  function titleCallback() {

  }
  public function content($administrator_block, $administrator_block_item) {
    $block = administrator_block_load($administrator_block);
    $blockItem = administrator_block_item_load($administrator_block_item);
    $cassiopeiaAdminAdministratorBlockItemDeleteForm = \Drupal::formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlockItemDeleteForm', $block, $blockItem);
    $build = [
      '#theme' => 'cassiopeia_admin_administrator_block_item_delete_page',
      '#cassiopeiaAdminAdministratorBlockItemDeleteForm' => $cassiopeiaAdminAdministratorBlockItemDeleteForm,
    ];
    return $build;
  }

}
