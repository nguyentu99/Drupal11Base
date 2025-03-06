<?php

namespace Drupal\cassiopeia_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Link;
use Drupal\Core\Url;

class CassiopeiaAdminAdministratorBlockDeleteForm extends FormBase {

  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'cassiopeia_admin_administrator_block_delete_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $block = NULL) {
    // TODO: Implement buildForm() method.
    if (!$form_state->has('#block')) {
      $form['#block'] = $block;
      $form_state->setFormState(['#block' => $block]);
    }
    else {
      $form['#block'] = $form_state->get('#block');
    }
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#prefix' => '<p>'.t('This action cannot be undone.').'</p>',
      '#suffix' => '&nbsp;&nbsp;' . Link::fromTextAndUrl(t('Cancel'), Url::fromUri('internal:/' . 'admin/cassiopeia/administrator/blocks'), [])->toString()
    );
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
    $block = $form_state->get('#block');
    if (!empty($block->id)) {
      administrator_block_delete($block->id);
      \Drupal::messenger()
        ->addMessage(t('Deleted @name block', array('@name' => $block->name)));
      $form_state->setRedirect('cassiopeia_admin.administrator_block');
    }else {

    }
  }

}
