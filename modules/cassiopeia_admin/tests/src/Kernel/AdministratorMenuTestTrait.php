<?php

declare(strict_types=1);

namespace Drupal\Tests\cassiopeia_admin\Kernel;

/**
 * Helpers for kernel tests using administrator config entities.
 */
trait AdministratorMenuTestTrait {

  /**
   * Creates a block config entity.
   */
  protected function createAdministratorBlock(string $id, string $name, string $icon = 'bi bi-grid', float $position = 0): void {
    $this->container->get('entity_type.manager')
      ->getStorage('administrator_block')
      ->create([
        'id' => $id,
        'name' => $name,
        'icon' => $icon,
        'position' => $position,
      ])
      ->save();
  }

  /**
   * Creates a block item config entity.
   */
  protected function createAdministratorBlockItem(
    string $id,
    string $block_id,
    string $name,
    string $link = '/admin',
    string $icon = 'bi bi-link',
    float $position = 0,
    ?string $url_meta = NULL,
  ): void {
    $this->container->get('entity_type.manager')
      ->getStorage('administrator_block_item')
      ->create([
        'id' => $id,
        'block_id' => $block_id,
        'name' => $name,
        'link' => $link,
        'icon' => $icon,
        'position' => $position,
        'url_meta' => $url_meta,
      ])
      ->save();
  }

}
