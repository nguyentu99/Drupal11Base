<?php

namespace Drupal\cassiopeia\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class TestForm extends FormBase {

  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'test_form';

  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // TODO: Implement buildForm() method.

    $form['example_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Example select field'),
      '#options' => [
        '1' => $this->t('One'),
        '2' => $this->t('Two'),
        '3' => $this->t('Three'),
        '4' => $this->t('From New York to Ger-ma-ny!'),
      ],
      '#ajax' => [
        'callback' => '::myAjaxCallback', // don't forget :: when calling a class method.
        //'callback' => [$this, 'myAjaxCallback'], //alternative notation
        'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
        'event' => 'change',
        'wrapper' => 'edit-output', // This element is updated with this AJAX callback.
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying entry...'),
        ],
      ]
    ];

    // Create a textbox that will be updated
    // when the user selects an item from the select box above.
    $form['output'] = [
      '#type' => 'textfield',
      '#size' => '60',
      '#disabled' => TRUE,
      '#value' => 'Hello, Drupal!!1',
      '#prefix' => '<div id="edit-output">',
      '#suffix' => '</div>',
    ];

    // Create the submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;

  }
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    // Return the prepared textfield.
    $form['output'] = [
      '#type' => 'textfield',
      '#size' => '60',
      '#disabled' => TRUE,
      '#value' => 'Hello, Drupal!!1',
      '#prefix' => '<div id="edit-output">',
      '#suffix' => '</div>',
    ];

    // If there's a value submitted for the select list let's set the textfield value.
    if ($selectedValue = $form_state->getValue('example_select')) {
      // Get the text of the selected option.
      $selectedText = $form['example_select']['#options'][$selectedValue];
      // Place the text of the selected option in our textfield.
      $form['output']['#value'] = $selectedText;
    }

    return $form['output'];
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

}
