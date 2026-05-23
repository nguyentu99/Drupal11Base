<?php

declare(strict_types=1);

namespace Drupal\cassiopeia_admin\Utility;

use Drupal\Component\Utility\Html;
use Drupal\cassiopeia_admin\Service\CassiopeiaAdminAdministrator;

/**
 * Sanitization helpers for administrator block/item form fields.
 */
final class AdministratorFieldHelper {

  /**
   * Sanitizes icon CSS class input for storage and display.
   */
  public static function sanitizeIcon(?string $icon): string {
    return CassiopeiaAdminAdministrator::sanitizeIconClass($icon);
  }

  /**
   * Builds escaped markup for the icon preview widget suffix.
   */
  public static function iconPreviewSuffix(?string $icon): string {
    $class = self::sanitizeIcon($icon);
    if ($class === '') {
      return '<div id="icon-demo"><i></i></div></div>';
    }
    return '<div id="icon-demo"><i class="' . Html::escape($class) . '"></i></div></div>';
  }

  /**
   * Escapes plain text for safe use in #markup table cells.
   */
  public static function escapeText(?string $text): string {
    return Html::escape((string) $text);
  }

  /**
   * Builds a safe icon preview cell for list tables.
   */
  public static function iconMarkup(?string $icon): string {
    $class = self::sanitizeIcon($icon);
    if ($class === '') {
      return '';
    }
    return '<i class="' . Html::escape($class) . '"></i>';
  }

}
