<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Language\LanguageInterface;

/**
 * Builds services landing page variables from a config_service node.
 */
class ConfigServiceContentProvider {

  /**
   * @return array<string, mixed>
   */
  public function build(?LanguageInterface $language = NULL): array {
    $language ??= \Drupal::languageManager()->getCurrentLanguage();
    $theme_path = '/' . \Drupal::service('extension.list.theme')->getPath('cassiopeia_theme');
    $node = cassiopeia_config_service_node($language);

    return cassiopeia_config_service_data($node, $theme_path, $language);
  }

}
