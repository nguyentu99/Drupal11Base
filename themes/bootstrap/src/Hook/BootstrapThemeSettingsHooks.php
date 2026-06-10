<?php

declare(strict_types=1);

namespace Drupal\bootstrap\Hook;

use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * OOP hook bridge for legacy Bootstrap theme-settings.php (Drupal 11.3+).
 */
class BootstrapThemeSettingsHooks {

  public function __construct(
    protected ThemeExtensionList $themeExtensionList,
  ) {}

  /**
   * Implements hook_form_system_theme_settings_alter().
   */
  #[Hook('form_system_theme_settings_alter')]
  public function formSystemThemeSettingsAlter(array &$form, FormStateInterface $form_state): void {
    $path = $this->themeExtensionList->getPath('bootstrap') . '/theme-settings.php';
    if (is_readable($path)) {
      require_once $path;
    }
    if (function_exists('bootstrap_form_system_theme_settings_alter')) {
      bootstrap_form_system_theme_settings_alter($form, $form_state);
    }
  }

}
