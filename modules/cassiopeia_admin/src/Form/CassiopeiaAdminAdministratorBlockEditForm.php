<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class CassiopeiaAdminAdministratorBlockEditForm extends FormBase {

  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'cassiopeia_admin_administrator_block_edit_form';

  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = null) {
    // TODO: Implement buildForm() method.
    $form['#attached']['library'][] = 'cassiopeia_admin/form.CassiopeiaAdminAdministratorBlockEditForm';
    if (!$form_state->has('#block')) {
      $form['#block'] = $block;
      $form_state->setFormState(array('#block'=> $block));
    }else {
      $form['#block'] = $form_state->get('#block');
    }

    $form['editBlock'] = array(
      '#type' => 'container',
    );
    $form['editBlock']['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Block name'),
      '#required' => TRUE,
      '#default_value' => empty($form['#block']) ? '' : $form['#block']->name,
    );
    $form['editBlock']['icon'] = array(
      '#type' => 'textfield',
      '#title' => 'Icon',
      '#prefix' => '<div id="select-icon">',
      '#suffix' => '<div id="icon-demo"><i class="fa ' . (empty($form['#block']) ? '' : 'customer-icon-' . $form['#block']->icon) . '"></i></div></div>',
      '#required' => TRUE,
      '#default_value' => empty($form['#block']) ? '' : $form['#block']->icon,
    );
    $form['editBlock']['position'] = array(
      '#type' => 'textfield',
      '#title' => t('Position'),
      '#required' => TRUE,
      '#default_value' => empty($form['#block']) ? 0 : $form['#block']->position,
    );
    $form['editBlock']['submit'] = array(
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
      $form_state->setRedirect('cassiopeia_admin.administrator_block');

    }
  }

}
