<?php

declare(strict_types=1);

namespace Drupal\Tests\cassiopeia_admin\Kernel;

use Drupal\cassiopeia_admin\Service\CassiopeiaAdminAdministrator;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests menu alter hooks (blocks + legacy menu alter).
 *
 * @group cassiopeia_admin
 */
class MenuAlterHookTest extends KernelTestBase {

  use AdministratorMenuTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'cassiopeia',
    'cassiopeia_admin',
    'cassiopeia_admin_test',
  ];

  /**
   * Alter hooks from cassiopeia_admin_test modify the built menu blocks.
   */
  public function testMenuAlterHooks(): void {
    $this->createAdministratorBlock('1', 'Original');
    $this->createAdministratorBlockItem('1', '1', 'Item', icon: 'bi bi-house');

    /** @var \Drupal\cassiopeia_admin\Service\CassiopeiaAdminAdministrator $administrator */
    $administrator = $this->container->get('cassiopeia_admin.administrator');
    $build = $administrator->lazyBuilder();
    $blocks = $build['administrator_menu']['#blocks'];

    $this->assertCount(2, $blocks);
    $this->assertSame('Legacy alter applied', $blocks[0]['label']);
    $this->assertSame('Test alter block', $blocks[1]['label']);
  }

}
