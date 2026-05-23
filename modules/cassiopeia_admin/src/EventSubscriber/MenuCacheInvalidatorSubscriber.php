<?php

declare(strict_types=1);

namespace Drupal\cassiopeia_admin\EventSubscriber;

use Drupal\cassiopeia_admin\Repository\AdministratorMenuRepository;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Invalidates administrator menu cache when routes or language config change.
 */
final class MenuCacheInvalidatorSubscriber implements EventSubscriberInterface {

  /**
   * Config name prefixes that affect menu URL or language context.
   */
  private const CONFIG_PREFIXES = [
    'language.',
    'locale.',
  ];

  public function __construct(
    protected AdministratorMenuRepository $menuRepository,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RoutingEvents::FINISHED => ['onRouteRebuild', -128],
      ConfigEvents::SAVE => ['onConfigSave', -128],
    ];
  }

  /**
   * Clears menu cache after route definitions are rebuilt.
   */
  public function onRouteRebuild(): void {
    $this->menuRepository->invalidateMenuCache();
  }

  /**
   * Clears menu cache when site language or locale settings change.
   */
  public function onConfigSave(ConfigCrudEvent $event): void {
    $name = $event->getConfig()->getName();
    if ($name === 'system.site') {
      $this->menuRepository->invalidateMenuCache();
      return;
    }
    foreach (self::CONFIG_PREFIXES as $prefix) {
      if (str_starts_with($name, $prefix)) {
        $this->menuRepository->invalidateMenuCache();
        return;
      }
    }
  }

}
