<?php

declare(strict_types=1);

namespace Drupal\Tests\cassiopeia\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests CassiopeiaRenderTemplate path validation (S-13).
 *
 * @group cassiopeia
 */
class CassiopeiaRenderTemplateTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'cassiopeia'];

  /**
   * Rejects paths outside the extension directory.
   */
  public function testRejectsPathTraversal(): void {
    $service = $this->container->get('cassiopeia.CassiopeiaRenderTemplate');
    $this->expectException(\InvalidArgumentException::class);
    $service->render('module', 'cassiopeia', '/templates/../../../core/core.services.yml', NULL);
  }

  /**
   * Renders a known module template when the path is valid.
   */
  public function testRendersValidTemplate(): void {
    $service = $this->container->get('cassiopeia.CassiopeiaRenderTemplate');
    $markup = $service->render('module', 'cassiopeia', '/templates/render-fixture.html.twig', ['ok' => TRUE]);
    $this->assertNotEmpty((string) $markup);
  }

}
