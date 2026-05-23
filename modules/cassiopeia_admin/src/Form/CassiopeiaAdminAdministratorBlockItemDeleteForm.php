<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Confirm deletion of an administrator block menu item.
 */
class CassiopeiaAdminAdministratorBlockItemDeleteForm extends FormBase {

  use AdministratorFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cassiopeia_admin_administrator_block_item_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL, $item = NULL) {
    $this->initBlockFormState($form_state, $block, $form);
    $this->initItemFormState($form_state, $item, $form);
    $block_obj = $this->getBlockFromFormState($form_state);

    $form['message'] = [
      '#markup' => '<p>' . $this->t('This action cannot be undone.') . '</p>',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#button_type' => 'danger',
    ];
    if ($block_obj && !empty($block_obj->id)) {
      $form['actions']['cancel'] = [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#url' => Url::fromRoute('cassiopeia_admin.administrator_block_items', [
          'administrator_block' => $block_obj->id,
        ]),
        '#attributes' => ['class' => ['button']],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$this->validateItemBlockOwnership($form_state)) {
      return;
    }
    $item = $this->getItemFromFormState($form_state);
    $block = $this->getBlockFromFormState($form_state);
    if (!empty($item->id)) {
      administrator_block_item_delete($item->id, $block->id);
      $this->messenger()->addMessage($this->t('Deleted @name.', [
        '@name' => $item->name,
      ]));
      $form_state->setRedirect('cassiopeia_admin.administrator_block_items', [
        'administrator_block' => $block->id,
      ]);
    }
  }

}
