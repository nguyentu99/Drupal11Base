<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\cassiopeia_admin\Utility\AdministratorFieldHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class CassiopeiaAdminAdministratorBlockItemsForm extends FormBase {

  use AdministratorFormTrait;

  public function getFormId() {
    return 'cassiopeia_admin_administrator_block_items_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL) {
    $this->initBlockFormState($form_state, $block, $form);
    $block_obj = $form['#block'];
    $result = \Drupal::service('cassiopeia_admin.menu_repository')
      ->loadItemsByBlock($block_obj->id);

    $form['items'] = [];
    $form['#tree'] = TRUE;
    $weight_delta = max(1, (int) round(count($result) / 2));
    foreach ($result as $item) {
      $form['items'][$item->id]['name'] = [
        '#type' => 'markup',
        '#markup' => AdministratorFieldHelper::escapeText($item->name),
      ];
      $form['items'][$item->id]['link'] = [
        '#type' => 'markup',
        '#markup' => AdministratorFieldHelper::escapeText($item->link),
      ];
      $form['items'][$item->id]['icon'] = [
        '#type' => 'markup',
        '#markup' => AdministratorFieldHelper::iconMarkup($item->icon),
      ];
      $form['items'][$item->id]['position'] = [
        '#type' => 'weight',
        '#default_value' => $item->position,
        '#delta' => $weight_delta,
        '#title_display' => 'invisible',
        '#attributes' => ['class' => ['items-weight']],
      ];
      $form['items'][$item->id]['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_item_delete', [
          'administrator_block' => $block_obj->id,
          'administrator_block_item' => $item->id,
        ], [
          'attributes' => [
            'class' => ['btn', 'btn-danger'],
            'alt' => $this->t('Remove this item'),
            'title' => $this->t('Remove this item'),
          ],
        ]),
      ];
      $form['items'][$item->id]['edit'] = [
        '#type' => 'link',
        '#title' => $this->t('Edit'),
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_item_edit', [
          'administrator_block' => $block_obj->id,
          'administrator_block_item' => $item->id,
        ], [
          'attributes' => [
            'class' => ['btn', 'btn-primary'],
            'alt' => $this->t('Edit this item'),
            'title' => $this->t('Edit this item'),
          ],
        ]),
      ];
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save changes'),
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state_values = $form_state->getValues();
    if (!empty($form_state_values['items']) && is_array($form_state_values['items'])) {
      foreach ($form_state_values['items'] as $key => $val) {
        if (is_numeric($key)) {
          $block = $this->getBlockFromFormState($form_state);
          administrator_block_item_position_update($key, $val['position'], $block->id);
        }
      }
    }
  }

}
