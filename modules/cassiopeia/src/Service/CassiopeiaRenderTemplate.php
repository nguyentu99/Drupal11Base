<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Render\Markup;

/**
 * Renders extension Twig templates programmatically.
 */
class CassiopeiaRenderTemplate {

  /**
   * Loads and renders a Twig template from an extension directory.
   */
  public function render(string $type, string $name, string $path, mixed $variables = NULL): Markup {
    $modulePath = \Drupal::service('extension.path.resolver')->getPath($type, $name);
    $path = $modulePath . $path;
    $template = \Drupal::service('twig')->load($path);
    $rendered = $template->render([
      'variables' => $variables,
    ]);
    return Markup::create($rendered);
  }

}
