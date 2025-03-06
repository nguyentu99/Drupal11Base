<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class CassiopeiaAdminAdministratorBlockItemEditForm extends FormBase {

  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'cassiopeia_admin_administrator_block_item_edit_form';

  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL, $item = null) {
    // TODO: Implement buildForm() method.
    $form['#attached']['library'][] = 'cassiopeia_admin/form.CassiopeiaAdminAdministratorBlockItemEditForm';
    if (!$form_state->has('#block')) {
      $form['#block'] = $block;
      $form_state->setFormState(['#block'=> $block]);
    }
    else {
      $form['#block'] = $form_state->get('#block');
    }
    if (!$form_state->has('#item')) {
      $form['#item'] = $item;
      $form_state->setFormState(['#item'=> $item]);
    }
    else {
      $form['#item'] = $form_state->get('#item');
    }


    $form['item'] = array(
      '#type' => 'container',
    );

    $form['item']['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Symbol name'),
      '#required' => TRUE,
      '#default_value' => empty($form['#item']) ? '' : $form['#item']->name,
    );
    $form['item']['link'] = array(
      '#type' => 'textfield',
      '#title' => t('Link'),
      '#required' => TRUE,
      '#default_value' => empty($form['#item']) ? '' : $form['#item']->link,
    );
    $form['item']['icon'] = array(
      '#type' => 'textfield',
      '#title' => 'Icon',
      '#prefix' => '<div id="select-icon">',
      '#suffix' => '<div id="icon-demo"><i class="fa ' . (empty($form['#item']) ? '' : 'customer-icon-' . $form['#item']->icon) . '"></i></div></div>',
      '#required' => TRUE,
      '#default_value' => empty($form['#item']) ? '' : $form['#item']->icon,
    );
    $form['item']['position'] = array(
      '#type' => 'textfield',
      '#title' => t('Position'),
      '#required' => TRUE,
      '#default_value' => empty($form['#item']) ? 0 : $form['#item']->position,
    );
    $form['item']['submit'] = array(
      '#type' => 'submit',
      '#value' => empty($form['#item']) ? t('Add new') : t('Update')
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
    $item = clone ((object)$form_state->get('#item'));
    $block = $form_state->get('#block');
    $form_state_values = $form_state->getValues();
    $item->name = trim($form_state_values['name']);
    $item->link = trim($form_state_values['link']);
    $item->icon = trim($form_state_values['icon']);
    $item->position = $form_state_values['position'];

    administrator_block_item_save($item);
    \Drupal::messenger()->addMessage(t('Updated @name symbol', array('@name'=> $form_state_values['name'])));
    $form_state->setRedirect('cassiopeia_admin.administrator_block_items', array('administrator_block' =>$block->id));

  }

}
