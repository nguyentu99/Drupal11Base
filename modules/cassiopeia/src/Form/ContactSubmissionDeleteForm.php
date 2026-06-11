<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes a contact submission.
 */
class ContactSubmissionDeleteForm extends ConfirmFormBase {

  protected ?array $submission = NULL;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'cassiopeia_contact_submission_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Delete contact submission from %name?', [
      '%name' => $this->submission['name'] ?? '',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('cassiopeia.contact_submissions');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?int $id = NULL): array {
    $this->submission = cassiopeia_contact_submission_load((int) $id);
    if (!$this->submission) {
      throw new NotFoundHttpException();
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    cassiopeia_contact_submission_delete((int) $this->submission['id']);
    $this->messenger()->addStatus($this->t('Contact submission deleted.'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
