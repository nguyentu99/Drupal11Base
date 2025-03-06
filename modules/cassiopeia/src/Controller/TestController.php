<?php



namespace Drupal\cassiopeia\Controller;

use Drupal\Core\Controller\ControllerBase;



class TestController extends ControllerBase {
  public function content() {
    $form = \Drupal::formBuilder()->getForm('Drupal\cassiopeia\Form\TestForm');
    $rendered = _cassiopeia_render_template_('module','cassiopeia','/templates/test.html.twig', $form);

    $build = [
      '#markup' => $rendered,
    ];
    return $build;
  }
}
