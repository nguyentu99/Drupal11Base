<?php

namespace Drupal\cassiopeia_admin\Controller;

use Drupal\cassiopeia_admin\Repository\AdministratorMenuRepository;
use Drupal\cassiopeia_admin\Service\AdministratorLinkMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Cassiopeia administration dashboard.
 */
class DashboardController extends ControllerBase {

  public function __construct(
    protected AdministratorMenuRepository $menuRepository,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('cassiopeia_admin.menu_repository'),
    );
  }

  /**
   * Dashboard page.
   */
  public function content(): array {
    $node_storage = $this->entityTypeManager()->getStorage('node');
    $published_nodes = $node_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->count()
      ->execute();
    $unpublished_nodes = $node_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', 0)
      ->count()
      ->execute();
    $user_count = $this->entityTypeManager()->getStorage('user')->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', 0, '>')
      ->count()
      ->execute();

    $stats = [
      [
        'label' => $this->t('Published content'),
        'value' => number_format((int) $published_nodes),
        'color' => 'primary',
        'icon' => 'bi-file-earmark-text',
        'url' => $this->urlIfAllowed('system.admin_content'),
      ],
      [
        'label' => $this->t('Unpublished content'),
        'value' => number_format((int) $unpublished_nodes),
        'color' => 'warning',
        'icon' => 'bi-file-earmark-minus',
        'url' => $this->urlIfAllowed('system.admin_content'),
      ],
      [
        'label' => $this->t('Users'),
        'value' => number_format((int) $user_count),
        'color' => 'success',
        'icon' => 'bi-people',
        'url' => $this->urlIfAllowed('entity.user.collection'),
      ],
      [
        'label' => $this->t('Website'),
        'value' => $this->t('View'),
        'color' => 'info',
        'icon' => 'bi-house',
        'url' => Url::fromRoute('<front>')->toString(),
      ],
    ];

    $link_groups = [];
    foreach ($this->menuRepository->loadMenuTreeRows() as $row) {
      $url = AdministratorLinkMetadata::toUrl($row->url_meta ?? NULL, $row->link);
      if (!$url || !$url->access($this->currentUser())) {
        continue;
      }

      $group_key = (string) $row->id;
      if (!isset($link_groups[$group_key])) {
        $link_groups[$group_key] = [
          'title' => $row->name,
          'links' => [],
        ];
      }

      $link_groups[$group_key]['links'][] = [
        'title' => $row->item_name,
        'url' => $url->toString(),
      ];
    }

    return [
      '#theme' => 'cassiopeia_admin_dashboard',
      '#welcome_title' => $this->t('Welcome, @name', ['@name' => $this->currentUser()->getDisplayName()]),
      '#welcome_message' => $this->t('Quick overview and shortcuts for managing the website.'),
      '#stats' => $stats,
      '#link_groups' => array_values($link_groups),
      '#attached' => [
        'library' => ['cassiopeia_admin/dashboard'],
      ],
      '#cache' => [
        'contexts' => ['user.permissions'],
        'tags' => ['cassiopeia_admin_menu:list'],
        'max-age' => 300,
      ],
    ];
  }

  /**
   * Returns a URL string when the current user may access the route.
   */
  private function urlIfAllowed(string $route_name): ?string {
    $url = Url::fromRoute($route_name);
    if (!$url->isRouted() || !$url->access($this->currentUser())) {
      return NULL;
    }
    return $url->toString();
  }

}
