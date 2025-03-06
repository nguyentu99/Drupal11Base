<?php

namespace Drupal\cassiopeia_admin\Service;

use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;

class CassiopeiaAdminAdministrator implements TrustedCallbackInterface{

  public function cassiopeia_admin_get_items_all() {
    $sql = "SELECT b.id, b.name as name, b.position, b.icon, i.id as item_id, i.name as item_name, i.link, i.icon as item_icon, i.position as item_position
      FROM {administrator_blocks} b JOIN {administrator_block_items} i ON b.id = i.bid ORDER BY b.position, i.position";
    $result = \Drupal::database()->query($sql)->fetchAll();
    return $result;
  }

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */
  public function cassiopeia_admin_get_administrator_menu($theme = '') {
//    $current_path = \Drupal::service('path.current')->getPath();
//    $current_route_name = \Drupal::routeMatch()->getRouteName();
//    $result = $this->cassiopeia_admin_get_items_all();
//    $list = [];
//    foreach ($result as $item) {
//      if (empty($list[$item->id])) {
//        //        $test_build = [
//        //          '#theme' => 'cassiopeia_admin_administrator_icon',
//        //          '#icon' => $item->icon,
//        //        ];
//        //        $test = \Drupal::service('renderer')->renderPlain($test_build);
//        //        print_r($test);
//        $list[$item->id] = [
//          //            '#title' => theme('cassiopeia_admin_administrator_icon', array('icon' => $item->icon)) . '<span>' . $item->name . '</span>',
//          '#title' => '<i class="' . $item->icon . '"></i> <p>' . $item->name . ' <i class="nav-arrow bi bi-chevron-right"></i></p>',
//          //          '#title' => \Drupal::service('renderer')->renderPlain([
//          //            '#theme' => 'cassiopeia_admin_administrator_icon',
//          //            '#icon' => $item->icon,
//          //          ]) . '<span>'.$item->name.'</span>',
//          '#href' => '#',
//          '#attributes' => ['class' => ['nav-link']],
//          '#localized_options' => ['html' => TRUE, 'fragment' => 'top'],
//        ];
//      }
//      if (!empty($list[$item->id])) {
//        $argument = explode('?', $item->link);
//        $url = \Drupal::service('path.validator')->getUrlIfValid($item->link);
//        if ($url) {
//          $args = [];
//          if (count($argument) == 2) {
//            $ex = explode('&', $argument[1]);
//            foreach ($ex as $part) {
//              $ex1 = explode('=', $part);
//              if (count($ex1) == 2) {
//                $args[$ex1[0]] = $ex1[1];
//              }
//            }
//          }
//          $attributes = [];
//          $attributes['class'] = ['nav-link'];
//          if ($current_route_name == $url->getRouteName()) {
//            $attributes['class'][] = 'active';
//          }
//
//          $list[$item->id]['#below'][$item->item_id] = [
//            //              '#title' => theme('cassiopeia_admin_administrator_icon', array('icon' => $item->item_icon)) . '<span>' . $item->item_name . '</span>',
//            '#title' => '<i class="' . $item->item_icon . '"></i> <p>' . $item->item_name . '</p>',
//            //            '#title' => \Drupal::service('renderer')->renderPlain([
//            //                '#theme' => 'cassiopeia_admin_administrator_icon',
//            //                '#icon' => $item->icon,
//            //              ]) . '<span>'.$item->name.'</span>',
//            '#href' => $argument[0],
//            '#path' => $argument[0],
//            '#url' => $url,
//            '#attributes' => $attributes,
//            '#localized_options' => ['html' => TRUE, 'query' => $args],
//          ];
//        }
//      }
//    }
//
//    foreach ($list as $block_key => $block_value) {
//      $active = FALSE;
//      if (!empty($block_value['#below'])) {
//        foreach ($block_value['#below'] as $item) {
//          if (!empty($item['#attributes']['class']) && in_array('active',$item['#attributes']['class'])) {
//            $active = TRUE;
//            break;
//          }
//        }
//      }
//      if ($active) {
//        $list[$block_key]['#attributes']['class'][] = 'active';
//      }
//    }

//    \Drupal::moduleHandler()
//      ->alter('cassiopeia_admin_administrator_menu', $list);
    //    print_r($list);
//    return [
//      '#theme' => 'cassiopeia_admin_administrator_menu',
//      '#items' => $list,
//    ];

    $build = [];
    $build['administrator_menu'] = [
      '#lazy_builder' => [
        self::class . '::lazyBuilder', [null]
      ],
      '#create_placeholder' => TRUE,
    ];
    return $build;
  }

  public function lazyBuilder($data) {
    $current_path = \Drupal::service('path.current')->getPath();
    $current_route_name = \Drupal::routeMatch()->getRouteName();
    $result = $this->cassiopeia_admin_get_items_all();
    $list = [];
    foreach ($result as $item) {
      if (empty($list[$item->id])) {
        //        $test_build = [
        //          '#theme' => 'cassiopeia_admin_administrator_icon',
        //          '#icon' => $item->icon,
        //        ];
        //        $test = \Drupal::service('renderer')->renderPlain($test_build);
        //        print_r($test);
        $list[$item->id] = [
          //            '#title' => theme('cassiopeia_admin_administrator_icon', array('icon' => $item->icon)) . '<span>' . $item->name . '</span>',
          '#title' => '<i class="' . $item->icon . '"></i> <p>' . $item->name . ' <i class="nav-arrow bi bi-chevron-right"></i></p>',
          //          '#title' => \Drupal::service('renderer')->renderPlain([
          //            '#theme' => 'cassiopeia_admin_administrator_icon',
          //            '#icon' => $item->icon,
          //          ]) . '<span>'.$item->name.'</span>',
          '#href' => '#',
          '#attributes' => ['class' => ['nav-link']],
          '#localized_options' => ['html' => TRUE, 'fragment' => 'top'],
        ];
      }
      if (!empty($list[$item->id])) {
        $argument = explode('?', $item->link);
        $url = \Drupal::service('path.validator')->getUrlIfValid($item->link);
        if ($url) {
          $args = [];
          if (count($argument) == 2) {
            $ex = explode('&', $argument[1]);
            foreach ($ex as $part) {
              $ex1 = explode('=', $part);
              if (count($ex1) == 2) {
                $args[$ex1[0]] = $ex1[1];
              }
            }
          }
          $attributes = [];
          $attributes['class'] = ['nav-link'];
          if ($current_route_name == $url->getRouteName()) {
            $attributes['class'][] = 'active';
          }

          $list[$item->id]['#below'][$item->item_id] = [
            //              '#title' => theme('cassiopeia_admin_administrator_icon', array('icon' => $item->item_icon)) . '<span>' . $item->item_name . '</span>',
            '#title' => '<i class="' . $item->item_icon . '"></i> <p>' . $item->item_name . '</p>',
            //            '#title' => \Drupal::service('renderer')->renderPlain([
            //                '#theme' => 'cassiopeia_admin_administrator_icon',
            //                '#icon' => $item->icon,
            //              ]) . '<span>'.$item->name.'</span>',
            '#href' => $argument[0],
            '#path' => $argument[0],
            '#url' => $url,
            '#attributes' => $attributes,
            '#localized_options' => ['html' => TRUE, 'query' => $args],
          ];
        }
      }
    }

    foreach ($list as $block_key => $block_value) {
      $active = FALSE;
      if (!empty($block_value['#below'])) {
        foreach ($block_value['#below'] as $item) {
          if (!empty($item['#attributes']['class']) && in_array('active',$item['#attributes']['class'])) {
            $active = TRUE;
            break;
          }
        }
      }
      if ($active) {
        $list[$block_key]['#attributes']['class'][] = 'active';
      }
    }


    $build = [];
    $build['administrator_menu'] = [
            '#theme' => 'cassiopeia_admin_administrator_menu',
            '#items' => $list,
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;

//     return [
//       '#theme' => 'cassiopeia_admin_administrator_menu',
//       '#items' => $list,
//     ];
  }

  public static function trustedCallbacks() {
    return [
      'lazyBuilder'
    ];
  }



}
