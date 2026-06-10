<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\cassiopeia_admin\Utility\AdministratorFieldHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CassiopeiaAdminAdministratorBlockEditForm extends FormBase {

  use AdministratorFormTrait;

  public function getFormId() {
    return 'cassiopeia_admin_administrator_block_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL) {
    $form['#attached']['library'][] = 'cassiopeia_admin/form.CassiopeiaAdminAdministratorBlockEditForm';
    $this->initBlockFormState($form_state, $block, $form);
    $block_obj = $form['#block'] ?? NULL;
    $icon = $block_obj->icon ?? NULL;

    $form['editBlock'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    $form['editBlock']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Block name'),
      '#required' => TRUE,
      '#default_value' => $block_obj->name ?? '',
    ];
    $form['editBlock']['icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon'),
      '#prefix' => '<div id="select-icon">',
      '#suffix' => AdministratorFieldHelper::iconPreviewSuffix($icon),
      '#required' => TRUE,
      '#default_value' => $icon ?? '',
    ];
    $form['editBlock']['position'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Position'),
      '#required' => TRUE,
      '#default_value' => $block_obj->position ?? 0,
    ];
    $form['editBlock']['submit'] = [
      '#type' => 'submit',
      '#value' => empty($block_obj->id) ? $this->t('Add new') : $this->t('Update'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('editBlock') ?? [];
    if (!isset($values['position']) || !is_numeric($values['position'])) {
      $form_state->setError($form['editBlock']['position'], $this->t('Position must be an integer'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('editBlock');
    $block = new \stdClass();
    $stored = $this->getBlockFromFormState($form_state);
    if ($stored !== NULL) {
      $block = clone $stored;
    }
    $is_new = empty($block->id);
    $block->name = trim($values['name']);
    $block->icon = AdministratorFieldHelper::sanitizeIcon($values['icon']);
    $block->position = (int) $values['position'];
    administrator_block_save($block);
    if ($is_new) {
      $this->messenger()->addMessage($this->t('Added new @name block', ['@name' => $values['name']]));
    }
    else {
      $this->messenger()->addMessage($this->t('Updated @name block', ['@name' => $values['name']]));
      $form_state->setRedirect('cassiopeia_admin.administrator_block');
    }
  }

}
