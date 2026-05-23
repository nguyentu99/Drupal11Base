<?php

declare(strict_types=1);

namespace Drupal\Tests\cassiopeia_admin\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests administrator menu config entity storage.
 *
 * @group cassiopeia_admin
 */
class ConfigStorageTest extends KernelTestBase {

  use AdministratorMenuTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'cassiopeia',
    'cassiopeia_admin',
  ];

  /**
   * Block and item CRUD via repository persists config entities.
   */
  public function testRepositoryConfigCrud(): void {
    $repository = $this->container->get('cassiopeia_admin.menu_repository');

    $block = (object) [
      'name' => 'Reports',
      'icon' => 'bi bi-folder',
      'position' => 1,
    ];
    $repository->saveBlock($block);
    $this->assertNotEmpty($block->id);

    $item = (object) [
      'bid' => $block->id,
      'name' => 'Overview',
      'link' => '/admin/reports',
      'icon' => 'bi bi-bar-chart',
      'position' => 0,
    ];
    $repository->saveItem($item);
    $this->assertNotEmpty($item->id);

    $rows = $repository->loadMenuTreeRows();
    $this->assertCount(1, $rows);
    $this->assertSame('Reports', $rows[0]->name);
    $this->assertSame('Overview', $rows[0]->item_name);

    $config = $this->config('cassiopeia_admin.administrator_block.' . $block->id);
    $this->assertSame('Reports', $config->get('name'));
  }

}
