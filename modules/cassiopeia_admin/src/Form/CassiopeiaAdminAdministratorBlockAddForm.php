<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class CassiopeiaAdminAdministratorBlockAddForm extends FormBase {

  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'cassiopeia_admin_administrator_block_add_form';

  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL) {
    // TODO: Implement buildForm() method.
    $form['#attached']['library'][] = 'cassiopeia_admin/form.CassiopeiaAdminAdministratorBlockAddForm';
    if (!$form_state->has('#block')) {
      $form['#block'] = $block;
      $form_state->setFormState(array('#block', $block));
    }else {
      $form['#block'] = $form_state->get('#block');
    }

    $form['addBlock'] = array(
      '#type' => 'container',
    );
    $form['addBlock']['name'] = array(
      '#type' => 'textfield',
      '#title' => 'Tên khối',
      '#required' => TRUE,
      '#default_value' => empty($form['#block']) ? '' : $form['#block']->name,
    );
    $form['addBlock']['icon'] = array(
      '#type' => 'textfield',
      '#title' => 'Icon',
      '#prefix' => '<div id="select-icon">',
      '#suffix' => '<div id="icon-demo"><i class="fa ' . (empty($form['#block']) ? '' : 'customer-icon-' . $form['#block']->icon) . '"></i></div></div>',
      '#required' => TRUE,
      '#default_value' => empty($form['#block']) ? '' : $form['#block']->icon,
    );
    $form['addBlock']['position'] = array(
      '#type' => 'textfield',
      '#title' => 'Vị trí',
      '#required' => TRUE,
      '#default_value' => empty($form['#block']) ? 0 : $form['#block']->position,
    );
    $form['addBlock']['submit'] = array(
      '#type' => 'submit',
      '#value' => empty($form['#block']) ? t('Add new') : t('Update')
    );
    return $form;

  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_state_values = $form_state->getValues();
    if (!is_numeric($form_state_values['position'])) {
      $form_state->setErrorByName('position', t('Position must be an integer'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
    $is_new = TRUE;
    $block = new \stdClass();
    $form_state_values = $form_state->getValues();
    if ($form_state->has('#block') && !empty($form_state->get('#block'))){
      $block = clone ((object)$form_state->get('#block'));
    }
    if (!empty($block->id)) {
      $is_new = FALSE;
    }
    $block->name = trim($form_state_values['name']);
    $block->icon = trim($form_state_values['icon']);
    $block->position = $form_state_values['position'];
    administrator_block_save($block);
    if ($is_new) {
      \Drupal::messenger()->addMessage(t('Added new @name block', array('@name' => $form_state_values['name'])));
    }
    else {
      \Drupal::messenger()->addMessage(t('Updated @name block', array('@name' => $form_state_values['name'])));
      $form_state->setRedirect('admin/cassiopeia/administrator/block');

    }
  }

}
