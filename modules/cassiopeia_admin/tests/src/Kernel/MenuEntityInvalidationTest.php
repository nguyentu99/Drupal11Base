<?php

declare(strict_types=1);

namespace Drupal\Tests\cassiopeia_admin\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests menu cache invalidation on user role entity changes.
 *
 * @group cassiopeia_admin
 */
class MenuEntityInvalidationTest extends KernelTestBase {

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('user_role');
  }

  /**
   * Saving a role clears the per-request menu row cache.
   */
  public function testRoleSaveInvalidatesRequestCache(): void {
    $this->createAdministratorBlock('1', 'Block');
    $this->createAdministratorBlockItem('1', '1', 'Home', icon: 'bi bi-house');

    /** @var \Drupal\cassiopeia_admin\Repository\AdministratorMenuRepository $repository */
    $repository = $this->container->get('cassiopeia_admin.menu_repository');
    $first = $repository->loadMenuTreeRows();
    $this->assertCount(1, $first);

    $role = Role::create([
      'id' => 'cassiopeia_test_role',
      'label' => 'Cassiopeia test',
    ]);
    $role->save();

    $second = $repository->loadMenuTreeRows();
    $this->assertCount(1, $second);
    $this->assertNotSame($first, $second);
  }

}
