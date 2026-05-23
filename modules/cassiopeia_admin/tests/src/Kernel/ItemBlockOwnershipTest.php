<?php

declare(strict_types=1);

namespace Drupal\Tests\cassiopeia_admin\Kernel;

use Drupal\cassiopeia_admin\ParamConverter\AdministratorBlockItemParamConverter;
use Drupal\cassiopeia_admin\Utility\AdministratorAccessHelper;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Routing\Route;

/**
 * Tests block/item ownership checks (S-06).
 *
 * @group cassiopeia_admin
 */
class ItemBlockOwnershipTest extends KernelTestBase {

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
   * Param converter returns NULL when item block_id does not match route block.
   */
  public function testParamConverterRejectsMismatchedBlock(): void {
    $this->createAdministratorBlock('1', 'A');
    $this->createAdministratorBlock('2', 'B');
    $this->createAdministratorBlockItem('1', '1', 'Item');

    $item = administrator_block_item_load('1');
    $block_wrong = administrator_block_load('2');
    $this->assertFalse(AdministratorAccessHelper::itemBelongsToBlock($item, $block_wrong));

    $converter = new AdministratorBlockItemParamConverter();
    $definition = ['type' => 'administrator_block_item'];
    $result = $converter->convert('1', $definition, 'administrator_block_item', [
      'administrator_block' => $block_wrong,
    ]);
    $this->assertNull($result);

    $block_right = administrator_block_load('1');
    $result = $converter->convert('1', $definition, 'administrator_block_item', [
      'administrator_block' => $block_right,
    ]);
    $this->assertNotNull($result);
    $this->assertSame('1', (string) $result->bid);
  }

  /**
   * Repository refuses position updates for items outside the block.
   */
  public function testRepositoryRejectsCrossBlockPositionUpdate(): void {
    $this->createAdministratorBlock('1', 'A');
    $this->createAdministratorBlock('2', 'B');
    $this->createAdministratorBlockItem('1', '1', 'Item', position: 0);

    $repository = $this->container->get('cassiopeia_admin.menu_repository');
    $repository->updateItemPosition('1', 99, '2');

    $item = administrator_block_item_load('1');
    $this->assertSame(0.0, (float) $item->position);
  }

}
