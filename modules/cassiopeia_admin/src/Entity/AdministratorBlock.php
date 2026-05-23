<?php

declare(strict_types=1);

namespace Drupal\cassiopeia_admin\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Administrator sidebar block.
 *
 * @ConfigEntityType(
 *   id = "administrator_block",
 *   label = @Translation("Administrator block"),
 *   label_collection = @Translation("Administrator blocks"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *   },
 *   config_prefix = "administrator_block",
 *   admin_permission = "cassiopeia admin block manager",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *   },
 *   config_export = {
 *     "id",
 *     "name",
 *     "icon",
 *     "position",
 *   }
 * )
 */
class AdministratorBlock extends ConfigEntityBase {

  /**
   * Block machine name (numeric string preserved from legacy DB).
   */
  protected $id;

  /**
   * Human-readable block title.
   */
  protected $name = '';

  /**
   * Icon CSS classes.
   */
  protected $icon = '';

  /**
   * Sort weight.
   */
  protected $position = 0;

  public function getIcon(): string {
    return $this->icon;
  }

  public function setIcon(string $icon): static {
    $this->icon = $icon;
    return $this;
  }

  public function getPosition(): float {
    return $this->position;
  }

  public function setPosition(float|int|string $position): static {
    $this->position = (float) $position;
    return $this;
  }

}
