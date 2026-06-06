<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Render\Markup;

/**
 * Renders extension Twig templates programmatically.
 */
class CassiopeiaRenderTemplate {

  public function __construct(
    protected ExtensionPathResolver $extensionPathResolver,
  ) {}

  /**
   * Loads and renders a Twig template from an extension directory.
   *
   * @throws \InvalidArgumentException
   *   When the template path is outside the extension or not a Twig file.
   */
  public function render(string $type, string $name, string $path, mixed $variables = NULL): Markup {
    if (!in_array($type, ['module', 'theme'], TRUE)) {
      throw new \InvalidArgumentException('Extension type must be module or theme.');
    }
    if ($path === '' || $path[0] !== '/' || str_contains($path, '..')) {
      throw new \InvalidArgumentException('Template path must be absolute and must not contain "..".');
    }
    if (!str_ends_with($path, '.html.twig') && !str_ends_with($path, '.twig')) {
      throw new \InvalidArgumentException('Template path must reference a .twig file.');
    }

    $base = $this->extensionPathResolver->getPath($type, $name);
    $full_path = $base . $path;
    $real_base = realpath($base);
    $real_full = realpath($full_path);
    if ($real_base === FALSE || $real_full === FALSE || !str_starts_with($real_full, $real_base . DIRECTORY_SEPARATOR)) {
      throw new \InvalidArgumentException('Template path must resolve inside the extension directory.');
    }

    $template = \Drupal::service('twig')->load($full_path);
    $rendered = $template->render([
      'variables' => $variables,
    ]);
    return Markup::create($rendered);
  }

}
