<?php
namespace Drupal\cassiopeia_admin\Service;
use Drupal\Core\Render\Markup;
class CassiopeiaRenderTemplate {
  public function render(string $type, string $name, string $path, mixed $variables = null) {
    $modulePath = \Drupal::service('extension.path.resolver')->getPath($type, $name);
    $path = $modulePath . $path;
    $template = \Drupal::service('twig')->load($path);
    $rendered = $template->render([
      'variables' => $variables
    ]);
    $rendered = Markup::create($rendered);
    return $rendered;
  }
}
