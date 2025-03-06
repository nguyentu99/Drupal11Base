<?php
namespace Drupal\cassiopeia\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;


class CassiopeiaController extends ControllerBase {
  public function pageNews() {

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('status', 1)
      ->accessCheck(TRUE)
      ->sort('created', 'DESC')
      ->execute();

    $news = Node::loadMultiple($query);
    $rendered = _cassiopeia_render_template_('module','cassiopeia','/templates/news.html.twig', $news);
    $build = [
      '#markup' => $rendered,
    ];

    return $build;
  }
}
