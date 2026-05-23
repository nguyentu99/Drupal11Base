<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\cassiopeia_admin\Utility\AdministratorFieldHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class CassiopeiaAdminAdministratorBlocksForm extends FormBase {

  public function getFormId() {
    return 'cassiopeia_admin_administrator_blocks_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $result = \Drupal::service('cassiopeia_admin.menu_repository')->loadAllBlocks();

    $form['blocks'] = [];
    $form['#tree'] = TRUE;
    $weight_delta = max(1, (int) round(count($result) / 2));
    foreach ($result as $item) {
      $form['blocks'][$item->id]['name'] = [
        '#type' => 'markup',
        '#markup' => AdministratorFieldHelper::escapeText($item->name),
      ];
      $form['blocks'][$item->id]['icon'] = [
        '#type' => 'markup',
        '#markup' => AdministratorFieldHelper::iconMarkup($item->icon),
      ];
      $form['blocks'][$item->id]['position'] = [
        '#type' => 'weight',
        '#default_value' => $item->position,
        '#delta' => $weight_delta,
        '#title_display' => 'invisible',
        '#attributes' => ['class' => ['block-weight']],
      ];
      $form['blocks'][$item->id]['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_delete', ['administrator_block' => $item->id], [
          'attributes' => [
            'class' => ['btn', 'btn-danger'],
            'alt' => $this->t('Remove this item'),
            'title' => $this->t('Remove this item'),
          ],
        ]),
      ];
      $form['blocks'][$item->id]['edit'] = [
        '#type' => 'link',
        '#title' => $this->t('Edit'),
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_edit', ['administrator_block' => $item->id], [
          'attributes' => [
            'class' => ['btn', 'btn-primary'],
            'alt' => $this->t('Edit this item'),
            'title' => $this->t('Edit this item'),
          ],
        ]),
      ];
      $form['blocks'][$item->id]['items'] = [
        '#type' => 'link',
        '#title' => $this->t('List'),
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_items', ['administrator_block' => $item->id], [
          'attributes' => [
            'class' => ['btn', 'btn-info'],
            'alt' => $this->t('List of this item'),
            'title' => $this->t('List of this item'),
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
    if (!empty($form_state_values['blocks']) && is_array($form_state_values['blocks'])) {
      foreach ($form_state_values['blocks'] as $key => $val) {
        if (is_numeric($key)) {
          administrator_block_position_update($key, $val['position']);
        }
      }
    }
  }

}
