<?php
namespace Drupal\cassiopeia\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
//    // Change path '/user/login' to '/login'.
//    if ($route = $collection->get('user.login')) {
//      $route->setPath('/login');
//      // $route->setDefault('_controller', '\Drupal\module_name\Controller\MyController::alter_edit_route');
//    }
//
//    // Always deny access to '/user/logout'.
//    // Note that the second parameter of setRequirement() is a string.
//    if ($route = $collection->get('user.logout')) {
//      $route->setRequirement('_access', 'FALSE');
//    }
//
//    // Disables the admin theme for user edit page.
//    if ($route = $collection->get('entity.user.edit_form')) {
//      $route->setOption('_admin_route', FALSE);
//    }
  }
}
