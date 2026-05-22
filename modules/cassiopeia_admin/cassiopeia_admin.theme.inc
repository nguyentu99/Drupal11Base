<?php

/**
 * @file
 * Theme preprocess functions for cassiopeia_admin.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for administrator blocks form template.
 */
function template_preprocess_cassiopeia_admin_administrator_blocks_form(array &$variables): void {
  $form = $variables['form'];
  $header = [
    t('Block name'),
    t('Icon'),
    t('Position'),
    t('Delete'),
    t('Edit'),
    t('List'),
  ];
  $rows = [];

  foreach (Element::children($form['blocks']) as $key) {
    $row = [];
    $row[] = ['data' => $form['blocks'][$key]['name']];
    $row[] = ['data' => $form['blocks'][$key]['icon']];
    $row[] = ['data' => $form['blocks'][$key]['position']];
    $row[] = ['data' => $form['blocks'][$key]['delete']];
    $row[] = ['data' => $form['blocks'][$key]['edit']];
    $row[] = ['data' => $form['blocks'][$key]['items']];
    $rows[] = ['data' => $row, 'class' => ['draggable']];
  }

  usort($rows, static function ($a, $b) {
    $a_weight = (is_array($a) && isset($a['data'][2]['data']['#value'])) ? $a['data'][2]['data']['#value'] : 0;
    $b_weight = (is_array($b) && isset($b['data'][2]['data']['#value'])) ? $b['data'][2]['data']['#value'] : 0;
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

/**
 * Prepares variables for administrator block items form template.
 */
function template_preprocess_cassiopeia_admin_administrator_block_items_form(array &$variables): void {
  $form = $variables['form'];
  $header = [
    t('Symbol name'),
    t('Link'),
    t('Icon'),
    t('Position'),
    t('Delete'),
    t('Edit'),
  ];
  $rows = [];

  foreach (Element::children($form['items']) as $key) {
    $row = [];
    $row[] = ['data' => $form['items'][$key]['name']];
    $row[] = ['data' => $form['items'][$key]['link']];
    $row[] = ['data' => $form['items'][$key]['icon']];
    $row[] = ['data' => $form['items'][$key]['position']];
    $row[] = ['data' => $form['items'][$key]['delete']];
    $row[] = ['data' => $form['items'][$key]['edit']];
    $rows[] = ['data' => $row, 'class' => ['draggable']];
  }

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
