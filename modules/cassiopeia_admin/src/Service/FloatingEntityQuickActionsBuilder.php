<?php

namespace Drupal\cassiopeia_admin\Service;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Builds floating quick-action links on entity detail pages.
 */
class FloatingEntityQuickActionsBuilder {

  /**
   * Routes where quick actions should appear.
   */
  private const DETAIL_ROUTES = [
    'entity.node.canonical',
    'entity.taxonomy_term.canonical',
  ];

  /**
   * Local task routes to expose as quick actions, in display order.
   */
  private const ALLOWED_TASK_ROUTES = [
    'entity.node.canonical',
    'entity.node.edit_form',
    'entity.node.version_history',
    'entity.node.content_translation_overview',
    'entity.taxonomy_term.canonical',
    'entity.taxonomy_term.edit_form',
    'entity.taxonomy_term.content_translation_overview',
  ];

  /**
   * Short Vietnamese labels for quick-action buttons.
   */
  private const SHORT_LABELS = [
    'entity.node.canonical' => 'Xem',
    'entity.node.edit_form' => 'Sửa',
    'entity.node.version_history' => 'Tóm tắt',
    'entity.node.content_translation_overview' => 'Dịch',
    'entity.taxonomy_term.canonical' => 'Xem',
    'entity.taxonomy_term.edit_form' => 'Sửa',
    'entity.taxonomy_term.content_translation_overview' => 'Dịch',
  ];

  public function __construct(
    private readonly RouteMatchInterface $routeMatch,
    private readonly AdminContext $adminContext,
    private readonly AccountInterface $currentUser,
    private readonly LocalTaskManagerInterface $localTaskManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Builds the floating quick-actions render array.
   */
  public function build(): array {
    if ($this->adminContext->isAdminRoute()) {
      return [];
    }
    if (!$this->currentUser->hasPermission('cassiopeia admin content manager')) {
      return [];
    }

    $route_name = $this->routeMatch->getRouteName();
    if (!in_array($route_name, self::DETAIL_ROUTES, TRUE)) {
      return [];
    }

    $entity = $this->getEntityFromRoute();
    if (!$entity instanceof EntityInterface) {
      return [];
    }

    $cacheability = new CacheableMetadata();
    $tasks = $this->localTaskManager->getLocalTasks($route_name, 0);
    $cacheability = $cacheability->merge($tasks['cacheability']);

    $actions = [];
    foreach (Element::getVisibleChildren($tasks['tabs']) as $plugin_id) {
      $tab = $tasks['tabs'][$plugin_id];
      $link = $tab['#link'] ?? NULL;
      if (!$link || empty($link['url'])) {
        continue;
      }

      $task_route = $link['url']->getRouteName();
      if (!in_array($task_route, self::ALLOWED_TASK_ROUTES, TRUE)) {
        continue;
      }

      $label = self::SHORT_LABELS[$task_route] ?? $link['title'];
      if (is_object($label) && method_exists($label, 'render')) {
        $label = (string) $label->render();
      }

      $actions[] = [
        'label' => (string) $label,
        'url' => $link['url']->toString(),
        'active' => !empty($tab['#active']),
        'route' => $task_route,
      ];
    }

    if ($actions === []) {
      return [];
    }

    usort($actions, static function (array $a, array $b): int {
      return array_search($a['route'], self::ALLOWED_TASK_ROUTES, TRUE)
        <=> array_search($b['route'], self::ALLOWED_TASK_ROUTES, TRUE);
    });

    $build = [
      '#theme' => 'cassiopeia_admin_floating_quick_actions',
      '#actions' => $actions,
      '#attached' => [
        'library' => ['cassiopeia_admin/floating_entity_quick_actions'],
      ],
      '#cache' => [
        'contexts' => ['user.permissions', 'route'],
        'tags' => $entity->getCacheTags(),
      ],
    ];
    $cacheability->applyTo($build);

    return $build;
  }

  /**
   * Loads the entity for the current canonical route.
   */
  private function getEntityFromRoute(): ?EntityInterface {
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof EntityInterface) {
      return $node;
    }
    if (is_scalar($node) && $node !== '') {
      return $this->entityTypeManager->getStorage('node')->load((int) $node);
    }

    $term = $this->routeMatch->getParameter('taxonomy_term');
    if ($term instanceof EntityInterface) {
      return $term;
    }
    if (is_scalar($term) && $term !== '') {
      return $this->entityTypeManager->getStorage('taxonomy_term')->load((int) $term);
    }

    return NULL;
  }

}
