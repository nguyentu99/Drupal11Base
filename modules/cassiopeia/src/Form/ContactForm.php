<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Public contact form on /lien-he.
 */
class ContactForm extends FormBase {

  public function __construct(
    protected EmailValidatorInterface $emailValidator,
    protected MailManagerInterface $mailManager,
    protected LanguageManagerInterface $languageManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('email.validator'),
      $container->get('plugin.manager.mail'),
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'cassiopeia_contact_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $settings = cassiopeia_contact_form_settings();

    $form['#attributes']['class'][] = 'contact-form';
    $form['#attached']['library'][] = 'cassiopeia/contact-form';

    $form['fields'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3']],
    ];

    $form['fields']['name'] = [
      '#type' => 'textfield',
      '#title' => Markup::create($settings['name_label'] . ' <span class="contact-form__required" aria-hidden="true">*</span>'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => $settings['name_placeholder'],
      ],
      '#wrapper_attributes' => ['class' => ['col-12']],
      '#label_attributes' => ['class' => ['form-label']],
    ];

    $form['fields']['email'] = [
      '#type' => 'email',
      '#title' => Markup::create($settings['email_label'] . ' <span class="contact-form__required" aria-hidden="true">*</span>'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => $settings['email_placeholder'],
      ],
      '#wrapper_attributes' => ['class' => ['col-12']],
      '#label_attributes' => ['class' => ['form-label']],
    ];

    $form['fields']['phone'] = [
      '#type' => 'tel',
      '#title' => Markup::create($settings['phone_label'] . ' <span class="contact-form__required" aria-hidden="true">*</span>'),
      '#required' => TRUE,
      '#maxlength' => 64,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => $settings['phone_placeholder'],
      ],
      '#wrapper_attributes' => ['class' => ['col-12']],
      '#label_attributes' => ['class' => ['form-label']],
    ];

    $form['fields']['message'] = [
      '#type' => 'textarea',
      '#title' => $settings['message_label'],
      '#rows' => 4,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => $settings['message_placeholder'],
      ],
      '#wrapper_attributes' => ['class' => ['col-12']],
      '#label_attributes' => ['class' => ['form-label']],
    ];

    $form['consent'] = [
      '#type' => 'checkbox',
      '#title' => cassiopeia_contact_form_consent_title($settings),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['form-check']],
      '#attributes' => ['class' => ['form-check-input']],
      '#label_attributes' => ['class' => ['form-check-label']],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $settings['submit_label'],
      '#attributes' => ['class' => ['btn-see-more', 'btn-see-more--solid', 'contact-form__submit']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $email = trim((string) $form_state->getValue('email'));
    if ($email !== '' && !$this->emailValidator->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('Enter a valid email address.'));
    }

    $phone = trim((string) $form_state->getValue('phone'));
    if ($phone !== '' && !preg_match('/^[0-9+\-\s().]{6,64}$/', $phone)) {
      $form_state->setErrorByName('phone', $this->t('Enter a valid phone number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);
    $submission_id = cassiopeia_contact_submission_save([
      'name' => trim((string) $form_state->getValue('name')),
      'email' => trim((string) $form_state->getValue('email')),
      'phone' => trim((string) $form_state->getValue('phone')),
      'message' => trim((string) $form_state->getValue('message')),
      'consent' => (int) (bool) $form_state->getValue('consent'),
      'langcode' => $language->getId(),
    ]);

    $settings = cassiopeia_contact_form_settings();
    if (!empty($settings['notify_email'])) {
      $this->mailManager->mail(
        'cassiopeia',
        'contact_submission',
        $settings['notify_email'],
        $language->getId(),
        ['submission' => cassiopeia_contact_submission_load($submission_id)],
      );
    }

    $this->messenger()->addStatus($settings['success_message']);
    $form_state->setRedirect('cassiopeia.contact');
  }

}
