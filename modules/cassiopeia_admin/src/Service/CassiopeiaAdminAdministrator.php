<?php

namespace Drupal\cassiopeia_admin\Service;

use Drupal\Component\Utility\Html;
use Drupal\cassiopeia_admin\Storage\AdministratorStorage;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathValidatorInterface;

/**
 * Builds the custom administrator sidebar menu.
 */
class CassiopeiaAdminAdministrator {

  public function __construct(
    protected AdministratorStorage $storage,
    protected CurrentPathStack $currentPath,
    protected PathValidatorInterface $pathValidator,
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * Builds render array for the administrator sidebar menu.
   */
  public function cassiopeia_admin_get_administrator_menu(): array {
    $current_route_name = \Drupal::routeMatch()->getRouteName();
    $result = $this->storage->loadBlocksWithItems();
    $list = [];

    foreach ($result as $item) {
      if (empty($list[$item->id])) {
        $list[$item->id] = [
          '#title' => '<i class="' . Html::escape($item->icon) . '"></i> <p>' . Html::escape($item->name) . ' <i class="nav-arrow bi bi-chevron-right"></i></p>',
          '#href' => '#',
          '#attributes' => ['class' => ['nav-link']],
          '#localized_options' => ['html' => TRUE],
          '#below' => [],
        ];
      }

      $url = $this->pathValidator->getUrlIfValid($item->link);
      if (!$url) {
        continue;
      }

      $query = [];
      $path_parts = explode('?', $item->link, 2);
      if (count($path_parts) === 2) {
        parse_str($path_parts[1], $query);
      }

      $attributes = ['class' => ['nav-link']];
      if ($current_route_name === $url->getRouteName()) {
        $attributes['class'][] = 'active';
      }

      $list[$item->id]['#below'][$item->item_id] = [
        '#title' => '<i class="' . Html::escape($item->item_icon) . '"></i> <p>' . Html::escape($item->item_name) . '</p>',
        '#url' => $url,
        '#attributes' => $attributes,
        '#localized_options' => ['html' => TRUE, 'query' => $query],
      ];
    }

    foreach ($list as $block_key => $block_value) {
      $active = FALSE;
      if (!empty($block_value['#below'])) {
        foreach ($block_value['#below'] as $menu_item) {
          if (!empty($menu_item['#attributes']['class']) && in_array('active', $menu_item['#attributes']['class'], TRUE)) {
            $active = TRUE;
            break;
          }
        }
      }
      if ($active) {
        $list[$block_key]['#attributes']['class'][] = 'active';
        $list[$block_key]['#attributes']['class'][] = 'menu-open';
      }
    }

    $this->moduleHandler->alter('cassiopeia_admin_administrator_menu', $list);

    return [
      '#theme' => 'cassiopeia_admin_administrator_menu',
      '#items' => $list,
      '#cache' => [
        'tags' => ['cassiopeia_admin_menu'],
        'contexts' => ['route'],
      ],
    ];
  }

}
