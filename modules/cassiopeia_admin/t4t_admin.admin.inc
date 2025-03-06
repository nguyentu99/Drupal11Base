<?php

function cassiopeia_admin_administrator_page_block() {
  $form = drupal_get_form('cassiopeia_admin_administrator_page_block_add');
  $form_load = drupal_get_form('cassiopeia_admin_administrator_page_block_load_all');
  return render($form) . render($form_load);
}

function cassiopeia_admin_administrator_page_block_edit($block) {
  $form = drupal_get_form('cassiopeia_admin_administrator_page_block_add', $block);
  return render($form);
}

function cassiopeia_admin_administrator_page_block_add($form, &$form_state) {
  drupal_add_js(drupal_get_path('module', 'cassiopeia_admin') . '/cassiopeia_admin.js');
  if (!empty($form_state['build_info']['args'][0])) {
    $form['#block'] = $form_state['build_info']['args'][0];
  }
  $form['taoblock'] = array(
    '#type' => 'fieldset',
    '#title' => 'Tạo khối',
  );
  $form['taoblock']['name'] = array(
    '#type' => 'textfield',
    '#title' => 'Tên khối',
    '#required' => TRUE,
    '#default_value' => empty($form['#block']) ? '' : $form['#block']->name,
  );
  $form['taoblock']['icon'] = array(
    '#type' => 'textfield',
    '#title' => 'Icon',
    '#prefix' => '<div id="select-icon">',
    '#suffix' => '<div id="icon-demo"><i class="fa ' . (empty($form['#block']) ? '' : 'customer-icon-' . $form['#block']->icon) . '"></i></div></div>',
    '#required' => TRUE,
    '#default_value' => empty($form['#block']) ? '' : $form['#block']->icon,
  );
  $form['taoblock']['position'] = array(
    '#type' => 'textfield',
    '#title' => 'Vị trí',
    '#required' => TRUE,
    '#default_value' => empty($form['#block']) ? 0 : $form['#block']->position,
  );
  $form['taoblock']['submit'] = array(
    '#type' => 'submit',
    '#value' => empty($form['#block']) ? 'Tạo mới' : 'Cập nhật'
  );
  return $form;
}

function cassiopeia_admin_administrator_page_block_add_validate($form, &$form_state) {
  if (!is_numeric($form_state['values']['position'])) {
    form_set_error('position', 'Vị trí phải là một số nguyên');
  }
}

function cassiopeia_admin_administrator_page_block_add_submit($form, &$form_state) {
  if (empty($form['#block'])) {
    _administrator_block_add(trim($form_state['values']['name']), trim($form_state['values']['icon']), $form_state['values']['position']);
    drupal_set_message('Đã thêm mới khối ' . $form_state['values']['name']);
  }
  else {
    _administrator_block_edit($form['#block'], $form_state['values']);
    drupal_set_message('Đã cập nhật khối ' . $form_state['values']['name']);
    $form_state['redirect'] = 'admin/cassiopeia/administrator/block';
  }
}

function cassiopeia_admin_administrator_page_block_load_all() {
  $sql = "SELECT id, name, icon, position FROM administrator_block ORDER BY position ASC";
  $result = db_query($sql)->fetchAll();
  $rows = array();
  $form['blocks'] = array();
  $form['#tree'] = TRUE;
  $weight_delta = round(count($result) / 2);
  foreach ($result as $item) {
    $form['blocks'][$item->id]['name'] = array(
      '#type' => 'markup',
      '#markup' => $item->name,
    );
    $form['blocks'][$item->id]['icon'] = array(
      '#type' => 'markup',
      '#markup' => theme('cassiopeia_admin_administrator_icon', array('icon' => $item->icon)),
    );
    $form['blocks'][$item->id]['position'] = array(
      '#type' => 'weight',
      '#default_value' => $item->position,
      '#delta' => $weight_delta,
      '#title_display' => 'invisible',
      '#attributes' => array('class' => array('block-weight')),
    );
    $form['blocks'][$item->id]['delete'] = array(
      '#type' => 'link',
      '#title' => 'Xóa',
      '#href' => 'admin/cassiopeia/administrator/block/' . $item->id . '/delete',
    );
    $form['blocks'][$item->id]['edit'] = array(
      '#type' => 'link',
      '#title' => 'Sửa',
      '#href' => 'admin/cassiopeia/administrator/block/' . $item->id . '/edit',
    );
    $form['blocks'][$item->id]['items'] = array(
      '#type' => 'link',
      '#title' => 'Danh sách',
      '#href' => 'admin/cassiopeia/administrator/block/' . $item->id . '/items',
    );
  }
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Lưu thay đổi',
  );
  return $form;
}

function cassiopeia_admin_administrator_page_block_load_all_submit($form, &$form_state) {
  foreach ($form_state['values']['blocks'] as $key => $val) {
    if (is_numeric($key)) {
      _administrator_block_position_update($key, $val);
    }
  }
}

function cassiopeia_admin_administrator_page_block_delete($block) {
  $form = drupal_get_form('cassiopeia_admin_administrator_page_block_delete_form', $block);
  return render($form);
}

function cassiopeia_admin_administrator_page_block_delete_form($form, &$form_state) {
  if (empty($form_state['build_info']['args'][0])) {
    return;
  }
  $form['#block'] = $form_state['build_info']['args'][0];
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Xóa',
    '#prefix' => '<p>Thao tác này không thể phục hồi.</p>',
    '#suffix' => '&nbsp;&nbsp;' . l('Quay lại', 'admin/cassiopeia/administrator/block')
  );
  return $form;
}

function cassiopeia_admin_administrator_page_block_delete_form_submit($form, &$form_state) {
  if (empty($form['#block'])) {
    return;
  }
  db_delete('administrator_block')
    ->condition('id', $form['#block']->id)
    ->execute();
  $form_state['redirect'] = 'admin/cassiopeia/administrator/block';
  drupal_set_message('Đã xóa ' . $form['#block']->name);
}

function cassiopeia_admin_administrator_page_block_items($block) {
  $form_item = drupal_get_form('cassiopeia_admin_administrator_page_block_add_items', $block);
  $form = drupal_get_form('cassiopeia_admin_administrator_page_block_load_items', $block);
  return render($form_item) . render($form);
}

function cassiopeia_admin_administrator_page_block_items_edit($block, $item) {
  $form_item = drupal_get_form('cassiopeia_admin_administrator_page_block_add_items', $block, $item);
  return render($form_item);
}

function cassiopeia_admin_administrator_page_block_add_items($form, &$form_state) {
  drupal_add_js(drupal_get_path('module', 'cassiopeia_admin') . '/cassiopeia_admin.js');
  if (!empty($form_state['build_info']['args'][0])) {
    $form['#block'] = $form_state['build_info']['args'][0];
  }
  if (!empty($form_state['build_info']['args'][1])) {
    $form['#items'] = $form_state['build_info']['args'][1];
  }
  $form['taoitems'] = array(
    '#type' => 'fieldset',
    '#title' => 'Tạo biểu tượng',
  );
  $form['taoitems']['name'] = array(
    '#type' => 'textfield',
    '#title' => 'Tên biểu tượng',
    '#required' => TRUE,
    '#default_value' => empty($form['#items']) ? '' : $form['#items']->name,
  );
  $form['taoitems']['link'] = array(
    '#type' => 'textfield',
    '#title' => 'Đường dẫn',
    '#required' => TRUE,
    '#default_value' => empty($form['#items']) ? '' : $form['#items']->link,
  );
  $form['taoitems']['icon'] = array(
    '#type' => 'textfield',
    '#title' => 'Icon',
    '#prefix' => '<div id="select-icon">',
    '#suffix' => '<div id="icon-demo"><i class="fa ' . (empty($form['#items']) ? '' : 'customer-icon-' . $form['#items']->icon) . '"></i></div></div>',
    '#required' => TRUE,
    '#default_value' => empty($form['#items']) ? '' : $form['#items']->icon,
  );
  $form['taoitems']['position'] = array(
    '#type' => 'textfield',
    '#title' => 'Vị trí',
    '#required' => TRUE,
    '#default_value' => empty($form['#items']) ? 0 : $form['#items']->position,
  );
  $form['taoitems']['submit'] = array(
    '#type' => 'submit',
    '#value' => empty($form['#items']) ? 'Tạo mới' : 'Cập nhật'
  );
  return $form;
}

function cassiopeia_admin_administrator_page_block_add_items_validate($form, &$form_state) {
  if (!is_numeric($form_state['values']['position'])) {
    form_set_error('position', 'Vị trí phải là một số nguyên');
  }
  if (!drupal_valid_path($form_state['values']['link'])) {
    form_set_error('link', 'Đường dẫn không chính xác');
  }
}

function cassiopeia_admin_administrator_page_block_add_items_submit($form, &$form_state) {
  if (empty($form['#items'])) {
    _administrator_block_items_add($form['#block']->id, trim($form_state['values']['name']), $form_state['values']['link'], $form_state['values']['icon'], $form_state['values']['position']);
    drupal_set_message('Đã thêm mới biểu tượng ' . $form_state['values']['name']);
  }
  else {
    _administrator_block_items_edit($form['#items'], $form_state['values']);
    drupal_set_message('Đã cập nhật biểu tượng ' . $form_state['values']['name']);
    $form_state['redirect'] = 'admin/cassiopeia/administrator/block/' . $form['#block']->id . '/items';
  }
}

function cassiopeia_admin_administrator_page_block_load_items($form, &$form_state) {
  $form['#block'] = $form_state['build_info']['args'][0];
  $sql = "SELECT id, name, link, icon, position FROM cassiopeia_admin_items WHERE bid = :bid ORDER BY position ASC";
  $result = db_query($sql, array(':bid' => $form['#block']->id))->fetchAll();
  $rows = array();
  $form['items'] = array();
  $form['#tree'] = TRUE;
  $weight_delta = round(count($result) / 2);
  foreach ($result as $item) {
    $form['items'][$item->id]['name'] = array(
      '#type' => 'markup',
      '#markup' => $item->name,
    );
    $form['items'][$item->id]['link'] = array(
      '#type' => 'markup',
      '#markup' => $item->link,
    );
    $form['items'][$item->id]['icon'] = array(
      '#type' => 'markup',
      '#markup' => theme('cassiopeia_admin_administrator_icon', array('icon' => $item->icon)),
    );
    $form['items'][$item->id]['position'] = array(
      '#type' => 'weight',
      '#default_value' => $item->position,
      '#delta' => $weight_delta,
      '#title_display' => 'invisible',
      '#attributes' => array('class' => array('items-weight')),
    );
    $form['items'][$item->id]['delete'] = array(
      '#type' => 'link',
      '#title' => 'Xóa',
      '#href' => 'admin/cassiopeia/administrator/block/' . $form['#block']->id . '/items/' . $item->id . '/delete',
    );
    $form['items'][$item->id]['edit'] = array(
      '#type' => 'link',
      '#title' => 'Sửa',
      '#href' => 'admin/cassiopeia/administrator/block/' . $form['#block']->id . '/items/' . $item->id . '/edit',
    );
  }
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Lưu thay đổi',
  );
  return $form;
}

function cassiopeia_admin_administrator_page_block_load_items_submit($form, &$form_state) {
  foreach ($form_state['values']['items'] as $key => $val) {
    if (is_numeric($key)) {
      _administrator_block_items_position_update($key, $val);
    }
  }
}

function cassiopeia_admin_administrator_page_block_items_delete($block, $item) {
  $form = drupal_get_form('cassiopeia_admin_administrator_page_block_items_delete_form', $block, $item);
  return render($form);
}

function cassiopeia_admin_administrator_page_block_items_delete_form($form, &$form_state) {
  if (empty($form_state['build_info']['args'][0]) && empty($form_state['build_info']['args'][1])) {
    return;
  }
  $form['#block'] = $form_state['build_info']['args'][0];
  $form['#item'] = $form_state['build_info']['args'][1];
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Xóa',
    '#prefix' => '<p>Thao tác này không thể phục hồi.</p>',
    '#suffix' => '&nbsp;&nbsp;' . l('Quay lại', 'admin/cassiopeia/administrator/block/' . $form['#block']->id . '/items')
  );
  return $form;
}

function cassiopeia_admin_administrator_page_block_items_delete_form_submit($form, &$form_state) {
  if (empty($form_state['build_info']['args'][0]) && empty($form_state['build_info']['args'][1])) {
    return;
  }
  $form['#block'] = $form_state['build_info']['args'][0];
  $form['#item'] = $form_state['build_info']['args'][1];
  if (empty($form['#block'])) {
    return;
  }
  db_delete('cassiopeia_admin_items')
    ->condition('id', $form['#item']->id)
    ->execute();
  $form_state['redirect'] = 'admin/cassiopeia/administrator/block/' . $form['#block']->id . '/items';
  drupal_set_message('Đã xóa ' . $form['#item']->name);
}
