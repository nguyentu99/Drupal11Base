<?php

namespace Drupal\cassiopeia_admin\Service;

use Drupal\cassiopeia_admin\Repository\AdministratorMenuRepository;
use Drupal\Component\Utility\Html;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Builds the Cassiopeia administrator sidebar menu.
 */
class CassiopeiaAdminAdministrator implements TrustedCallbackInterface {

  /**
   * Cache tag for the administrator menu render cache.
   */
  public const MENU_CACHE_TAG = 'cassiopeia_admin_menu:list';

  /**
   * Default max-age for menu render cache (1 hour).
   */
  private const MENU_CACHE_MAX_AGE = 3600;

  public function __construct(
    protected AdministratorMenuRepository $menuRepository,
    protected ModuleHandlerInterface $moduleHandler,
    protected RouteMatchInterface $routeMatch,
  ) {}

  /**
   * @deprecated in cassiopeia_admin:8.x-1.x and is no longer used.
   *   Use AdministratorMenuRepository::loadMenuTreeRows() instead.
   */
  public static function resetMenuRowsCache(): void {
    \Drupal::service('cassiopeia_admin.menu_repository')->resetRequestCache();
  }

  /**
   * Loads all block/item rows (delegates to repository).
   */
  public function cassiopeia_admin_get_items_all(): array {
    return $this->menuRepository->loadMenuTreeRows();
  }

  /**
   * Builds the lazy-loaded administrator menu render array.
   */
  public function cassiopeia_admin_get_administrator_menu(string $theme = ''): array {
    return [
      'administrator_menu' => [
        '#lazy_builder' => ['cassiopeia_admin.administrator:lazyBuilder', []],
        '#create_placeholder' => TRUE,
      ],
      '#cache' => [
        'keys' => ['cassiopeia_admin', 'menu', 'wrapper'],
        'tags' => [self::MENU_CACHE_TAG],
        'contexts' => ['user.permissions', 'languages:language_interface'],
        'max-age' => self::MENU_CACHE_MAX_AGE,
      ],
    ];
  }

  /**
   * Lazy builder callback for the administrator sidebar menu.
   */
  public function lazyBuilder(): array {
    $current_route_name = $this->routeMatch->getRouteName();
    $blocks = [];
    $block_index = [];

    foreach ($this->menuRepository->loadMenuTreeRows() as $row) {
      if (!isset($block_index[$row->id])) {
        $block_index[$row->id] = count($blocks);
        $blocks[] = [
          'label' => $row->name,
          'icon_class' => self::sanitizeIconClass($row->icon),
          'open' => FALSE,
          'links' => [],
        ];
      }

      $url = AdministratorLinkMetadata::toUrl($row->url_meta ?? NULL, $row->link);
      if (!$url) {
        continue;
      }

      $link_classes = ['nav-link'];
      if ($current_route_name && $current_route_name === $url->getRouteName()) {
        $link_classes[] = 'active';
        $blocks[$block_index[$row->id]]['open'] = TRUE;
      }

      $icon_class = self::sanitizeIconClass($row->item_icon);
      $blocks[$block_index[$row->id]]['links'][] = [
        '#type' => 'link',
        '#title' => $row->item_name,
        '#url' => $url,
        '#attributes' => ['class' => $link_classes],
        '#prefix' => $icon_class !== '' ? Markup::create('<i class="' . Html::escape($icon_class) . '"></i> ') : '',
      ];
    }

    $this->moduleHandler->alter('cassiopeia_admin_menu_blocks', $blocks);
    // @deprecated in cassiopeia_admin:8.x-1.x — use hook_cassiopeia_admin_menu_blocks_alter().
    $this->moduleHandler->alter('cassiopeia_admin_menu', $blocks);

    return [
      'administrator_menu' => [
            '#theme' => 'cassiopeia_admin_administrator_menu',
        '#blocks' => $blocks,
      '#cache' => [
          'keys' => ['cassiopeia_admin', 'menu', 'tree'],
          'tags' => [self::MENU_CACHE_TAG],
          'contexts' => ['route', 'user.permissions', 'languages:language_interface'],
          'max-age' => self::MENU_CACHE_MAX_AGE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return [
      'lazyBuilder',
    ];
  }

  /**
   * Restricts icon field values to safe CSS class characters.
   */
  public static function sanitizeIconClass(?string $icon): string {
    if ($icon === NULL || $icon === '') {
      return '';
    }
    return preg_replace('/[^a-zA-Z0-9_\- ]/', '', $icon) ?? '';
  }

}
