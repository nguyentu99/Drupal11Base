<?php

declare(strict_types=1);

namespace Drupal\Tests\cassiopeia_admin\Kernel;

use Drupal\cassiopeia_admin\Service\CassiopeiaAdminAdministrator;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests administrator menu cache invalidation.
 *
 * @group cassiopeia_admin
 */
class MenuCacheTest extends KernelTestBase {

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
   * Menu repository invalidation clears request cache; deletes refresh tree.
   */
  public function testMenuCacheInvalidation(): void {
    $this->createAdministratorBlock('1', 'Test block');
    $this->createAdministratorBlockItem('1', '1', 'Dashboard');

    /** @var \Drupal\cassiopeia_admin\Repository\AdministratorMenuRepository $repository */
    $repository = $this->container->get('cassiopeia_admin.menu_repository');

    $rows = $repository->loadMenuTreeRows();
    $this->assertCount(1, $rows);

    $this->assertSame($rows, $repository->loadMenuTreeRows());

    $repository->invalidateMenuCache();

    $reloaded = $repository->loadMenuTreeRows();
    $this->assertCount(1, $reloaded);
    $this->assertNotSame($rows, $reloaded);

    $repository->deleteBlock('1');
    $this->assertCount(0, $repository->loadMenuTreeRows());

    $this->assertSame(
      CassiopeiaAdminAdministrator::MENU_CACHE_TAG,
      'cassiopeia_admin_menu:list'
    );
  }

}
