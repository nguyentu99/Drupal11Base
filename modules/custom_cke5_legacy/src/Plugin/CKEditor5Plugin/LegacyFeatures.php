<?php

declare(strict_types=1);

namespace Drupal\custom_cke5_legacy\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\editor\EditorInterface;

/**
 * Dynamic configuration for legacy CKEditor formatting features.
 */
class LegacyFeatures extends CKEditor5PluginDefault {

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    // Inline font styles are only compatible with unrestricted text formats.
    // Formats using "Limit allowed HTML tags" always strip `style` attributes.
    if ($editor->getFilterFormat()->getHtmlRestrictions() !== FALSE) {
      return $static_plugin_config;
    }

    return $static_plugin_config + [
      'htmlSupport' => [
        'allow' => [
          [
            'name' => 'span',
            'styles' => TRUE,
            'attributes' => TRUE,
            'classes' => TRUE,
          ],
          [
            'name' => [
              'regexp' => [
                'pattern' => '/^(p|h[1-6])$/',
              ],
            ],
            'styles' => TRUE,
            'classes' => TRUE,
          ],
        ],
      ],
    ];
  }

}
