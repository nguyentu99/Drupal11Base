<?php



namespace Drupal\cassiopeia\Controller;

use Drupal\Core\Controller\ControllerBase;



class TestController extends ControllerBase {
  public function content() {
    $form = \Drupal::formBuilder()->getForm('Drupal\cassiopeia\Form\TestForm');
    $rendered = \Drupal::service('cassiopeia.CassiopeiaRenderTemplate')
      ->render('module', 'cassiopeia', '/templates/test.html.twig', $form);

    $build = [
      '#markup' => $rendered,
    ];
    return $build;
  }
}
