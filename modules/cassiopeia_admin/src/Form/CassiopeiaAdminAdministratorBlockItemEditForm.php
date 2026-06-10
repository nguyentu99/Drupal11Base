<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\cassiopeia_admin\Service\AdministratorLinkMetadata;
use Drupal\cassiopeia_admin\Utility\AdministratorFieldHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CassiopeiaAdminAdministratorBlockItemEditForm extends FormBase {

  use AdministratorFormTrait;

  public function getFormId() {
    return 'cassiopeia_admin_administrator_block_item_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL, $item = NULL) {
    $form['#attached']['library'][] = 'cassiopeia_admin/form.CassiopeiaAdminAdministratorBlockItemEditForm';
    $this->initBlockFormState($form_state, $block, $form);
    $this->initItemFormState($form_state, $item, $form);
    $item_obj = $form['#item'] ?? NULL;
    $icon = $item_obj->icon ?? NULL;

    $form['item'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    $form['item']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Symbol name'),
      '#required' => TRUE,
      '#default_value' => $item_obj->name ?? '',
    ];
    $form['item']['link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link'),
      '#required' => TRUE,
      '#default_value' => $item_obj->link ?? '',
    ];
    $form['item']['icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon'),
      '#prefix' => '<div id="select-icon">',
      '#suffix' => AdministratorFieldHelper::iconPreviewSuffix($icon),
      '#required' => TRUE,
      '#default_value' => $icon ?? '',
    ];
    $form['item']['position'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Position'),
      '#required' => TRUE,
      '#default_value' => $item_obj->position ?? 0,
    ];
    $form['item']['submit'] = [
      '#type' => 'submit',
      '#value' => empty($item_obj->id) ? $this->t('Add new') : $this->t('Update'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('item') ?? [];
    if (!isset($values['position']) || !is_numeric($values['position'])) {
      $form_state->setError($form['item']['position'], $this->t('Position must be an integer'));
    }
    if (empty($values['link']) || !\Drupal::service('path.validator')->isValid($values['link'])) {
      $form_state->setErrorByName('link', $this->t('Incorrect link'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$this->validateItemBlockOwnership($form_state)) {
      return;
    }
    $values = $form_state->getValue('item');
    $item = clone $this->getItemFromFormState($form_state);
    $block = $this->getBlockFromFormState($form_state);
    $item->name = trim($values['name']);
    $item->link = trim($values['link']);
    $item->icon = AdministratorFieldHelper::sanitizeIcon($values['icon']);
    $item->position = (int) $values['position'];
    $item->url_meta = AdministratorLinkMetadata::encodeStorage($item->link);
    administrator_block_item_save($item);
    $this->messenger()->addMessage($this->t('Updated @name symbol', ['@name' => $values['name']]));
    $form_state->setRedirect('cassiopeia_admin.administrator_block_items', [
      'administrator_block' => $block->id,
    ]);
  }

}
