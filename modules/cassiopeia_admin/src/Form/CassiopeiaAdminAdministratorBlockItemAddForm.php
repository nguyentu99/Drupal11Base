<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class CassiopeiaAdminAdministratorBlockItemAddForm extends FormBase {

  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'cassiopeia_admin_administrator_block_item_add_form';

  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL) {
    // TODO: Implement buildForm() method.
    $form['#attached']['library'][] = 'cassiopeia_admin/form.CassiopeiaAdminAdministratorBlockItemAddForm';
    if (!$form_state->has('#block')) {
      $form['#block'] = $block;
      $form_state->setFormState(['#block'=> $block]);
    }
    else {
      $form['#block'] = $form_state->get('#block');
    }

    $form['item'] = array(
      '#type' => 'container',
    );

    $form['item']['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Symbol name'),
      '#required' => TRUE,
//      '#default_value' => empty($form['#items']) ? '' : $form['#items']->name,
    );
    $form['item']['link'] = array(
      '#type' => 'textfield',
      '#title' => t('Link'),
      '#required' => TRUE,
//      '#default_value' => empty($form['#items']) ? '' : $form['#items']->link,
    );
    $form['item']['icon'] = array(
      '#type' => 'textfield',
      '#title' => 'Icon',
      '#prefix' => '<div id="select-icon">',
      '#suffix' => '<div id="icon-demo"><i class="fa ' . (empty($form['#items']) ? '' : 'customer-icon-' . $form['#items']->icon) . '"></i></div></div>',
      '#required' => TRUE,
//      '#default_value' => empty($form['#items']) ? '' : $form['#items']->icon,
    );
    $form['item']['position'] = array(
      '#type' => 'textfield',
      '#title' => t('Position'),
      '#required' => TRUE,
      '#default_value' => 0,
    );
    $form['item']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Add new')
    );
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('item') ?? [];
    if (!is_numeric($values['position'] ?? '')) {
      $form_state->setErrorByName('item][position', $this->t('Position must be an integer'));
    }
    if (!\Drupal::service('path.validator')->isValid($values['link'] ?? '')) {
      $form_state->setErrorByName('item][link', $this->t('Incorrect link'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $item = new \stdClass();
    $block = $form['#block'];
    $values = $form_state->getValue('item');
    $item->bid = $block->id;
    $item->name = trim($values['name']);
    $item->link = trim($values['link']);
    $item->icon = trim($values['icon']);
    $item->position = $values['position'];
    administrator_block_item_save($item);
    $this->messenger()->addStatus($this->t('Added new @name symbol', ['@name' => $values['name']]));
    $form_state->setRedirect('cassiopeia_admin.administrator_block_items', [
      'administrator_block' => $block->id,
    ]);
  }

}
