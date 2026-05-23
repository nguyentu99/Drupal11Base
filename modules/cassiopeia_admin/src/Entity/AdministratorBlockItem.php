<?php

declare(strict_types=1);

namespace Drupal\cassiopeia_admin\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Administrator sidebar menu item within a block.
 *
 * @ConfigEntityType(
 *   id = "administrator_block_item",
 *   label = @Translation("Administrator block item"),
 *   label_collection = @Translation("Administrator block items"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *   },
 *   config_prefix = "administrator_block_item",
 *   admin_permission = "cassiopeia admin block manager",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *   },
 *   config_export = {
 *     "id",
 *     "block_id",
 *     "name",
 *     "link",
 *     "icon",
 *     "position",
 *     "url_meta",
 *   }
 * )
 */
class AdministratorBlockItem extends ConfigEntityBase {

  protected $id;

  protected $block_id = '';

  protected $name = '';

  protected $link = '';

  protected $icon = '';

  protected $position = 0;

  protected $url_meta;

  public function getBlockId(): string {
    return $this->block_id;
  }

  public function setBlockId(string $block_id): static {
    $this->block_id = $block_id;
    return $this;
  }

  public function getLink(): string {
    return $this->link;
  }

  public function setLink(string $link): static {
    $this->link = $link;
    return $this;
  }

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

  public function getUrlMeta(): ?string {
    return $this->url_meta;
  }

  public function setUrlMeta(?string $url_meta): static {
    $this->url_meta = $url_meta;
    return $this;
  }

}
