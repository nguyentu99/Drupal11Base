<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class CassiopeiaAdminAdministratorBlockItemsForm extends FormBase {

  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'cassiopeia_admin_administrator_block_items_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL) {
    // TODO: Implement buildForm() method.
//    $form['#attached']['library'][] = 'cassiopeia_admin/form.AdministratorBlockAddForm';
//    if (!$form_state->has('#block')) {
//      $form['#block'] = $block;
//      $form_state->setFormState(['#block' => $block]);
//    }
//    else {
//      $form['#block'] = $form_state->get('#block');
//    }
//
//    $form['addBlock'] = [
//      '#type' => 'container',
//      //      '#title' => 'Tạo khối',
//    ];
//    $form['addBlock']['name'] = [
//      '#type' => 'textfield',
//      '#title' => 'Tên khối',
//      '#required' => TRUE,
//      '#default_value' => empty($form['#block']) ? '' : $form['#block']->name,
//    ];
//    $form['addBlock']['icon'] = [
//      '#type' => 'textfield',
//      '#title' => 'Icon',
//      '#prefix' => '<div id="select-icon">',
//      '#suffix' => '<div id="icon-demo"><i class="fa ' . (empty($form['#block']) ? '' : 'customer-icon-' . $form['#block']->icon) . '"></i></div></div>',
//      '#required' => TRUE,
//      '#default_value' => empty($form['#block']) ? '' : $form['#block']->icon,
//    ];
//    $form['addBlock']['position'] = [
//      '#type' => 'textfield',
//      '#title' => 'Vị trí',
//      '#required' => TRUE,
//      '#default_value' => empty($form['#block']) ? 0 : $form['#block']->position,
//    ];
//    $form['addBlock']['submit'] = [
//      '#type' => 'submit',
//      '#value' => empty($form['#block']) ? 'Tạo mới' : 'Cập nhật'
//    ];
//
//    $form['#theme'] = 'cassiopeia_admin_administrator_block_add_form';
//
//    return $form;

    if (!$form_state->has('#block')) {
      $form['#block'] = $block;
      $form_state->setFormState(['#block' => $block]);
    }
    else {
      $form['#block'] = $form_state->get('#block');
    }
    $sql = "SELECT id, name, link, icon, position FROM administrator_block_items WHERE bid = :bid ORDER BY position ASC";
    $result = \Drupal::database()->query($sql, array(':bid' => $form['#block']->id))->fetchAll();

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
//        '#markup' => theme('cassiopeia_admin_administrator_icon', array('icon' => $item->icon)),
        '#markup' => '<i class="fa '.$item->icon.'"></i>',
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
        '#title' => t('Delete'),
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_item_delete', ['administrator_block'=>$form['#block']->id, 'administrator_block_item' => $item->id], [
          'attributes' => [
            'class' => [
              'btn',
              'btn-danger'
            ],
            'alt' => t('Remove this item'),
            'title' => t('Remove this item'),
          ],
        ]),
//        '#url' => 'admin/cassiopeia/administrator/block/' . $form['#block']->id . '/items/' . $item->id . '/delete',
      );
      $form['items'][$item->id]['edit'] = array(
        '#type' => 'link',
        '#title' => t('Edit'),
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_item_edit', ['administrator_block'=>$form['#block']->id, 'administrator_block_item' => $item->id], [
          'attributes' => [
            'class' => [
              'btn',
              'btn-primary'
            ],
            'alt' => t('Remove this item'),
            'title' => t('Remove this item'),
          ],
        ]),
      );
    }
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save changes'),
    );
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
    $form_state_values = $form_state->getValues();
    foreach ($form_state_values['items'] as $key => $val) {
      if (is_numeric($key)) {
        administrator_block_item_position_update($key, $val['position']);
      }
    }
  }

}
