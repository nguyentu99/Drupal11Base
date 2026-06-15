<?php

namespace Drupal\cassiopeia\Plugin\ImcePlugin;

use Drupal\imce\ImceFM;
use Drupal\imce\ImcePluginBase;

/**
 * Attaches the modern IMCE skin on the file manager page.
 *
 * @ImcePlugin(
 *   id = "cassiopeia_modern_ui",
 *   label = "Cassiopeia Modern UI",
 *   weight = 100,
 * )
 */
class ModernUi extends ImcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildPage(array &$page, ImceFM $fm): void {
    $page['#attached']['library'][] = 'cassiopeia/imce-modern';
  }

}
