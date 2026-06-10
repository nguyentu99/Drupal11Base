<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\cassiopeia_admin\Service\AdministratorLinkMetadata;
use Drupal\cassiopeia_admin\Utility\AdministratorFieldHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CassiopeiaAdminAdministratorBlockItemAddForm extends FormBase {

  use AdministratorFormTrait;

  public function getFormId() {
    return 'cassiopeia_admin_administrator_block_item_add_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL) {
    $form['#attached']['library'][] = 'cassiopeia_admin/form.CassiopeiaAdminAdministratorBlockItemAddForm';
    $this->initBlockFormState($form_state, $block, $form);

    $form['item'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    $form['item']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Symbol name'),
      '#required' => TRUE,
    ];
    $form['item']['link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link'),
      '#required' => TRUE,
    ];
    $form['item']['icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon'),
      '#prefix' => '<div id="select-icon">',
      '#suffix' => AdministratorFieldHelper::iconPreviewSuffix(NULL),
      '#required' => TRUE,
    ];
    $form['item']['position'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Position'),
      '#required' => TRUE,
      '#default_value' => 0,
    ];
    $form['item']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add new'),
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
    $values = $form_state->getValue('item');
    $block = $this->getBlockFromFormState($form_state);
    $item = new \stdClass();
    $item->bid = $block->id;
    $item->name = trim($values['name']);
    $item->link = trim($values['link']);
    $item->icon = AdministratorFieldHelper::sanitizeIcon($values['icon']);
    $item->position = (int) $values['position'];
    $item->url_meta = AdministratorLinkMetadata::encodeStorage($item->link);
    administrator_block_item_save($item);
    $this->messenger()->addMessage($this->t('Added new @name symbol', ['@name' => $values['name']]));
    $form_state->setRedirect('cassiopeia_admin.administrator_block_items', [
      'administrator_block' => $block->id,
    ]);
  }

}
