<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class CassiopeiaAdminAdministratorBlockDeleteForm extends FormBase {

  use AdministratorFormTrait;

  public function getFormId() {
    return 'cassiopeia_admin_administrator_block_delete_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL) {
    $this->initBlockFormState($form_state, $block, $form);
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
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('cassiopeia_admin.administrator_block'),
      '#attributes' => ['class' => ['button']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $block = $this->getBlockFromFormState($form_state);
    if (!empty($block->id)) {
      administrator_block_delete($block->id);
      $this->messenger()->addMessage($this->t('Deleted @name block', ['@name' => $block->name]));
      $form_state->setRedirect('cassiopeia_admin.administrator_block');
    }
  }

}
