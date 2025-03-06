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
    $form_state_values = $form_state->getValues();
    if (!is_numeric($form_state_values['position'])) {
      $form_state->setErrorByName('position', t('Position must be an integer'));
    }
    if (!\Drupal::service('path.validator')->isValid($form_state_values['link'])) {
      $form_state->setErrorByName('link', t('Incorrect link'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
    $item = new \stdClass();
    $block = $form_state->get('#block');
    $form_state_values = $form_state->getValues();
    $item->bid = $block->id;
    $item->name = trim($form_state_values['name']);
    $item->link = trim($form_state_values['link']);
    $item->icon = trim($form_state_values['icon']);
    $item->position = $form_state_values['position'];
    administrator_block_item_save($item);
    \Drupal::messenger()->addMessage(t('Added new @name symbol', array('@name'=> $form_state_values['name'])));

  }

}
