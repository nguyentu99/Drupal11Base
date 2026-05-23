<?php

declare(strict_types=1);

namespace Drupal\Tests\cassiopeia_admin\Kernel;

use Drupal\cassiopeia_admin\Service\CassiopeiaAdminAdministrator;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests lazy builder render cache metadata.
 *
 * @group cassiopeia_admin
 */
class MenuLazyBuilderCacheTest extends KernelTestBase {

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
   * Menu wrapper and lazy tree include cache tags and max-age.
   */
  public function testMenuRenderCacheMetadata(): void {
    $this->createAdministratorBlock('1', 'Block');
    $this->createAdministratorBlockItem('1', '1', 'Home', icon: 'bi bi-house');

    /** @var \Drupal\cassiopeia_admin\Service\CassiopeiaAdminAdministrator $administrator */
    $administrator = $this->container->get('cassiopeia_admin.administrator');

    $wrapper = $administrator->cassiopeia_admin_get_administrator_menu();
    $this->assertSame(3600, $wrapper['#cache']['max-age']);
    $this->assertContains(CassiopeiaAdminAdministrator::MENU_CACHE_TAG, $wrapper['#cache']['tags']);

    $tree = $administrator->lazyBuilder();
    $menu = $tree['administrator_menu'];
    $this->assertSame('cassiopeia_admin_administrator_menu', $menu['#theme']);
    $this->assertContains(CassiopeiaAdminAdministrator::MENU_CACHE_TAG, $menu['#cache']['tags']);
    $this->assertContains('route', $menu['#cache']['contexts']);
    $this->assertContains('user.permissions', $menu['#cache']['contexts']);
  }

}
