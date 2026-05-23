<?php

declare(strict_types=1);

namespace Drupal\cassiopeia_admin\Utility;

use Drupal\cassiopeia_admin\Entity\AdministratorBlock;
use Drupal\cassiopeia_admin\Entity\AdministratorBlockItem;

/**
 * Maps config entities to stdClass records used by legacy forms and routes.
 */
final class AdministratorRecordMapper {

  public static function blockToRecord(AdministratorBlock $entity): object {
    return (object) [
      'id' => $entity->id(),
      'name' => $entity->label(),
      'icon' => $entity->getIcon(),
      'position' => $entity->getPosition(),
    ];
  }

  public static function itemToRecord(AdministratorBlockItem $entity): object {
    return (object) [
      'id' => $entity->id(),
      'bid' => $entity->getBlockId(),
      'name' => $entity->label(),
      'link' => $entity->getLink(),
      'icon' => $entity->getIcon(),
      'position' => $entity->getPosition(),
      'url_meta' => $entity->getUrlMeta(),
    ];
  }

  public static function menuRowFromEntities(AdministratorBlock $block, AdministratorBlockItem $item): object {
    return (object) [
      'id' => $block->id(),
      'name' => $block->label(),
      'position' => $block->getPosition(),
      'icon' => $block->getIcon(),
      'item_id' => $item->id(),
      'item_name' => $item->label(),
      'link' => $item->getLink(),
      'item_icon' => $item->getIcon(),
      'item_position' => $item->getPosition(),
      'url_meta' => $item->getUrlMeta(),
    ];
  }

}
