<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Confirm deletion of an administrator block item.
 */
class CassiopeiaAdminAdministratorBlockItemDeleteForm extends FormBase {

  public function getFormId() {
    return 'cassiopeia_admin_administrator_block_item_delete_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL, $blockItem = NULL) {
    $form['#block'] = $block;
    $form['#block_item'] = $blockItem;

    $form['message'] = [
      '#markup' => '<p>' . $this->t('Are you sure you want to delete %name?', ['%name' => $blockItem->name ?? '']) . '</p>',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#button_type' => 'danger',
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_items', [
        'administrator_block' => $block->id,
      ]),
      '#attributes' => ['class' => ['button']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $block = $form['#block'];
    $blockItem = $form['#block_item'];

    if (!empty($blockItem->id)) {
      administrator_block_item_delete($blockItem->id);
      $this->messenger()->addStatus($this->t('Deleted @name symbol', ['@name' => $blockItem->name]));
      $form_state->setRedirect('cassiopeia_admin.administrator_block_items', [
        'administrator_block' => $block->id,
      ]);
    }
  }

}
