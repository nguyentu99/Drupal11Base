<?php

declare(strict_types=1);

namespace Drupal\cassiopeia_admin\Utility;

/**
 * Validates administrator block/item ownership for IDOR prevention.
 */
final class AdministratorAccessHelper {

  /**
   * Whether an item belongs to the given block.
   */
  public static function itemBelongsToBlock(?object $item, object|string|int|null $block): bool {
    if ($item === NULL || !isset($item->bid)) {
      return FALSE;
    }
    $block_id = is_object($block) ? ($block->id ?? NULL) : $block;
    if ($block_id === NULL || $block_id === '') {
      return FALSE;
    }
    return (string) $item->bid === (string) $block_id;
  }

}
