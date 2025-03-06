<?php

use Drupal\Core\Render\Element;

function template_preprocess_cassiopeia_admin_administrator_blocks_form(&$variables){
  $form = $variables['form'];
  $header = array(t('Block name'), 'Icon', t('Position'), t('Delete'), t('Edit'), t('List'));
  $rows = array();
  foreach (Element::children($form['blocks']) as $key) {
    $row = array();
    $row[] = array('data'=>$form['blocks'][$key]['name']) ;
    $row[] = array('data'=>$form['blocks'][$key]['icon']);
    $row[] = array('data'=>$form['blocks'][$key]['position']);
    $row[] = array('data'=>$form['blocks'][$key]['delete']);
    $row[] = array('data'=>$form['blocks'][$key]['edit']);
    $row[] = array('data'=>$form['blocks'][$key]['items']);

    $rows[] = array('data' => $row, 'class'=> array('draggable'));
  }

  usort($rows, function($a, $b) {
    $a_weight = (is_array($a) && isset($a['data'][2]['data']['#value']) ? $a['data'][2]['data']['#value'] : 0);
    $b_weight = (is_array($b) && isset($b['data'][2]['data']['#value']) ? $b['data'][2]['data']['#value'] : 0);
    return $a_weight - $b_weight;
  });

  $variables['table'] = [
    '#type' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#attributes' => [
      'id' => 'cassiopeia-admin-block',
      'class' => ['arrange'],
    ],
    '#tabledrag' => [
      [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'block-weight',
      ],
    ],
  ];
}

function template_preprocess_cassiopeia_admin_administrator_block_items_form(&$variables){
  $form = $variables['form'];
  $header = array(t('Symbol name'),t('Link'), 'Icon', t('Position'), t('Delete'), t('Edit'));
  $rows = array();

  foreach (Element::children($form['items']) as $key) {
    $row = array();
    $row[] = array('data'=>$form['items'][$key]['name']) ;
    $row[] = array('data'=>$form['items'][$key]['link']) ;
    $row[] = array('data'=>$form['items'][$key]['icon']);
    $row[] = array('data'=>$form['items'][$key]['position']);
    $row[] = array('data'=>$form['items'][$key]['delete']);
    $row[] = array('data'=>$form['items'][$key]['edit']);
    $rows[] = array('data' => $row, 'class'=> array('draggable'));
  }

//  usort($rows, function($a, $b) {
//    $a_weight = (is_array($a) && isset($a['data'][3]['data']['#value']) ? $a['data'][3]['data']['#value'] : 0);
//    $b_weight = (is_array($b) && isset($b['data'][3]['data']['#value']) ? $b['data'][3]['data']['#value'] : 0);
//    return $a_weight - $b_weight;
//  });

  $variables['table'] = [
    '#type' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#attributes' => [
      'id' => 'cassiopeia-admin-block-items',
      'class' => ['arrange'],
    ],
    '#tabledrag' => [
      [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'items-weight',
      ],
    ],
  ];

}

function  template_preprocess_cassiopeia_admin_administrator_menu(&$variables) {

}


function template_preprocess_cassiopeia_admin_administrator_icon(&$variables){
//  print_r($variables);
//  exit();
//  $icon = $variables['icon'];
//  return '<i class="'.$icon.'"></i>';
}
