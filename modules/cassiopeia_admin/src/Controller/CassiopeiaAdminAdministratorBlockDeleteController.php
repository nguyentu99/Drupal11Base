<?php

namespace Drupal\cassiopeia_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

class CassiopeiaAdminAdministratorBlockDeleteController extends ControllerBase {
  public  function titleCallback($administrator_block) {
    $block = administrator_block_load($administrator_block);
    return 'Delete block '.$block->name;
  }
  public function content($administrator_block) {
    $block = administrator_block_load($administrator_block);
    $cassiopeiaAdminAdministratorBlockDeleteForm = \Drupal::formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlockDeleteForm', $block);
    $build = [
      '#theme' => 'cassiopeia_admin_administrator_block_delete_page',
      '#cassiopeiaAdminAdministratorBlockDeleteForm' => $cassiopeiaAdminAdministratorBlockDeleteForm,
    ];
    return $build;
  }
}
