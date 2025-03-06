<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class CassiopeiaAdminAdministratorBlocksForm extends FormBase {

  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'cassiopeia_admin_administrator_blocks_form';

  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $query = \Drupal::database()->select('administrator_blocks', 'c');
    $query->fields('c', ['id', 'name', 'icon', 'position']);
    $query->distinct();
    $result = $query->execute()->fetchAll();


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
        '#markup' => '<i class="fa '.$item->icon.'"></i>',
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
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_delete', ['administrator_block'=>$item->id], [
          'attributes' => [
            'class' => [
              'btn',
              'btn-danger'
            ],
            'alt' => t('Remove this item'),
            'title' => t('Remove this item'),
          ],
        ]),
      );
      $form['blocks'][$item->id]['edit'] = array(
        '#type' => 'link',
        '#title' => t('Edit'),
//        '#href' => 'admin/cassiopeia/administrator/block/' . $item->id . '/edit',
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_edit', ['administrator_block'=>$item->id], [
          'attributes' => [
            'class' => [
              'btn',
              'btn-primary',
            ],
            'alt' => t('Edit this item'),
            'title' => t('Edit this item'),
          ],
        ]),
      );
      $form['blocks'][$item->id]['items'] = array(
        '#type' => 'link',
        '#title' => t('List'),
//        '#href' => 'admin/cassiopeia/administrator/block/' . $item->id . '/items',
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_items', ['administrator_block'=>$item->id], [
          'attributes' => [
            'class' => [
              'btn',
              'btn-info',
            ],
            'alt' => t('List of this item'),
            'title' => t('List of this item'),
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

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement validateForm() method.

  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
    $form_state_values = $form_state->getValues();
//    print_r($form_state_values['blocks']);
//    exit();
    if (!empty($form_state_values['blocks']) && is_array($form_state_values['blocks'])) {
      foreach ($form_state_values['blocks'] as $key => $val) {
        if (is_numeric($key)) {
          administrator_block_position_update($key, $val['position']);
        }
      }
    }
  }

}
