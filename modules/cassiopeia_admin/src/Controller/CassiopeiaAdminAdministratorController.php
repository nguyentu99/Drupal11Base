<?php

namespace Drupal\cassiopeia_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Render\Markup;

class CassiopeiaAdminAdministratorController extends ControllerBase {

  public function content() {
    $cassiopeiaAdminAdministratorBlockAddForm = \Drupal::formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlockAddForm');
//    $administratorBlockAddForm = \Drupal::service('renderer')->render($administratorBlockAddForm);

    $cassiopeiaAdminAdministratorBlocksForm = \Drupal::formBuilder()->getForm('Drupal\cassiopeia_admin\Form\CassiopeiaAdminAdministratorBlocksForm');
//    $administratorBlocksForm = \Drupal::service('renderer')->render($administratorBlocksForm);

//    $rendered = _cassiopeia_render_template_('module','cassiopeia','/templates/test.html.twig', $form);

//    $form_load = drupal_get_form('cassiopeia_admin_administrator_page_block_load_all');
//    return render($form) . render($form_load);

//    print_r($administratorBlocksForm);
//    $rendered = (string)$administratorBlockAddForm . (string)$administratorBlocksForm  ;


//    $build = [
//      'administratorBlockAddForm' => ['#markup'=>$administratorBlockAddForm],
//      'administratorBlocksForm' => ['#markup'=>$administratorBlocksForm],
//    ];

    $build = [
      '#theme' => 'cassiopeia_admin_administrator_blocks_page',
      '#cassiopeiaAdminAdministratorBlockAddForm' => $cassiopeiaAdminAdministratorBlockAddForm,
      '#cassiopeiaAdminAdministratorBlocksForm' => $cassiopeiaAdminAdministratorBlocksForm,
    ];
    return $build;
  }

}
