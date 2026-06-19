<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Form;

use Drupal\cassiopeia\Service\CassiopeiaConfigManagedFile;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Site-wide Cassiopeia page configuration.
 */
class CassiopeiaConfigForm extends ConfigFormBase {

  public function __construct(
    ConfigFactoryInterface $config_factory,
    TypedConfigManagerInterface $typed_config_manager,
    private readonly CassiopeiaConfigManagedFile $configManagedFile,
  ) {
    parent::__construct($config_factory, $typed_config_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('cassiopeia.config_managed_file'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'cassiopeia_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['cassiopeia.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('cassiopeia.settings');

    $form['#tree'] = TRUE;
    $form['tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Page settings'),
    ];

    $form['contact_strip'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact strip (footer)'),
      '#group' => 'tabs',
    ];
    $form['contact_strip']['phone_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone label'),
      '#default_value' => $config->get('contact_strip.phone_label'),
    ];
    $form['contact_strip']['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone number'),
      '#default_value' => $config->get('contact_strip.phone'),
    ];
    $form['contact_strip']['phone_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone link'),
      '#description' => $this->t('Used in tel: links, e.g. +84866505509'),
      '#default_value' => $config->get('contact_strip.phone_link'),
    ];
    $form['contact_strip']['email_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email label'),
      '#default_value' => $config->get('contact_strip.email_label'),
    ];
    $form['contact_strip']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#default_value' => $config->get('contact_strip.email'),
    ];
    $form['contact_strip']['social_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Social media label'),
      '#default_value' => $config->get('contact_strip.social_label'),
    ];
    $form['contact_strip']['facebook_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Facebook URL'),
      '#default_value' => $config->get('contact_strip.facebook_url'),
    ];
    $form['contact_strip']['youtube_url'] = [
      '#type' => 'url',
      '#title' => $this->t('YouTube URL'),
      '#default_value' => $config->get('contact_strip.youtube_url'),
    ];
    $form['contact_strip']['linkedin_url'] = [
      '#type' => 'url',
      '#title' => $this->t('LinkedIn URL'),
      '#default_value' => $config->get('contact_strip.linkedin_url'),
    ];
    $form['contact_strip']['map_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map label'),
      '#default_value' => $config->get('contact_strip.map_label'),
    ];
    $form['contact_strip']['address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Address'),
      '#rows' => 3,
      '#default_value' => $config->get('contact_strip.address'),
    ];
    $form['contact_strip']['map_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Google Maps URL'),
      '#default_value' => $config->get('contact_strip.map_url'),
    ];

    $form['cta_banner'] = [
      '#type' => 'details',
      '#title' => $this->t('CTA banner (homepage)'),
      '#group' => 'tabs',
    ];
    $form['cta_banner']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config->get('cta_banner.title'),
    ];
    $form['cta_banner']['lead'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Lead text'),
      '#rows' => 3,
      '#default_value' => $config->get('cta_banner.lead'),
    ];
    $form['cta_banner']['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone number'),
      '#default_value' => $config->get('cta_banner.phone'),
    ];
    $form['cta_banner']['phone_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone link'),
      '#default_value' => $config->get('cta_banner.phone_link'),
    ];
    $form['cta_banner']['quote_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Quote button URL'),
      '#default_value' => $config->get('cta_banner.quote_url'),
    ];
    $form['cta_banner']['video_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background video path'),
      '#description' => $this->t('Relative path from site root, e.g. /themes/cassiopeia_theme/images/video-thuml.mp4'),
      '#default_value' => $config->get('cta_banner.video_path'),
    ];

    $form['contact_page'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact page'),
      '#group' => 'tabs',
    ];
    $form['contact_page']['hero_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hero title'),
      '#default_value' => $config->get('contact_page.hero_title'),
    ];
    $form['contact_page']['hero_lead'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Hero lead'),
      '#rows' => 3,
      '#default_value' => $config->get('contact_page.hero_lead'),
    ];
    $form['contact_page']['hero_image'] = $this->managedFileElement([
      '#type' => 'managed_file',
      '#title' => $this->t('Hero image'),
      '#upload_location' => 'public://cassiopeia/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg webp'],
      ],
    ], 'contact_page.hero_image', (int) $config->get('contact_page.hero_image'));
    $form['contact_page']['aside_title'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Aside title'),
      '#rows' => 2,
      '#default_value' => $config->get('contact_page.aside_title'),
    ];
    $form['contact_page']['aside_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Aside description'),
      '#rows' => 4,
      '#default_value' => $config->get('contact_page.aside_description'),
    ];
    $form['contact_page']['aside_image'] = $this->managedFileElement([
      '#type' => 'managed_file',
      '#title' => $this->t('Aside background image'),
      '#upload_location' => 'public://cassiopeia/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg webp'],
      ],
    ], 'contact_page.aside_image', (int) $config->get('contact_page.aside_image'));
    $form['contact_page']['map_embed'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Google Maps embed URL'),
      '#rows' => 2,
      '#default_value' => $config->get('contact_page.map_embed'),
    ];
    $form['contact_page']['intl_section_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('International contact section title'),
      '#default_value' => $config->get('contact_page.intl_section_title'),
    ];

    $qr_codes = $config->get('contact_page.qr_codes') ?: [];
    $form['contact_page']['qr_codes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('QR codes'),
    ];
    for ($i = 0; $i < 5; $i++) {
      $item = $qr_codes[$i] ?? ['label' => '', 'image' => 0, 'logo' => 0];
      $form['contact_page']['qr_codes'][$i] = [
        '#type' => 'details',
        '#title' => $this->t('QR @num', ['@num' => $i + 1]),
        '#open' => $i === 0,
      ];
      $form['contact_page']['qr_codes'][$i]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $item['label'] ?? '',
      ];
      $form['contact_page']['qr_codes'][$i]['image'] = $this->managedFileElement([
        '#type' => 'managed_file',
        '#title' => $this->t('QR image'),
        '#upload_location' => 'public://cassiopeia/',
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg webp'],
        ],
      ], "contact_page.qr_codes.$i.image", (int) ($item['image'] ?? 0));
      $form['contact_page']['qr_codes'][$i]['logo'] = $this->managedFileElement([
        '#type' => 'managed_file',
        '#title' => $this->t('Optional logo overlay'),
        '#upload_location' => 'public://cassiopeia/',
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg webp svg'],
        ],
      ], "contact_page.qr_codes.$i.logo", (int) ($item['logo'] ?? 0));
    }

    $form['contact_page']['address_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address section title'),
      '#default_value' => $config->get('contact_page.address_title'),
    ];
    $form['contact_page']['office_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Office name'),
      '#default_value' => $config->get('contact_page.office_name'),
    ];
    $form['contact_page']['address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Address'),
      '#rows' => 3,
      '#default_value' => $config->get('contact_page.address'),
    ];
    $form['contact_page']['address_map_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Address Google Maps URL'),
      '#default_value' => $config->get('contact_page.address_map_url'),
    ];

    $form['contact_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact form'),
      '#group' => 'tabs',
    ];
    $form['contact_form']['notify_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Notification email'),
      '#description' => $this->t('Leave empty to disable email notifications.'),
      '#default_value' => $config->get('contact_form.notify_email'),
    ];
    $form['contact_form']['terms_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Terms URL'),
      '#default_value' => $config->get('contact_form.terms_url'),
    ];
    $form['contact_form']['consent_before'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consent text (before link)'),
      '#default_value' => $config->get('contact_form.consent_before'),
    ];
    $form['contact_form']['consent_link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consent link text'),
      '#default_value' => $config->get('contact_form.consent_link_text'),
    ];
    $form['contact_form']['consent_after'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consent text (after link)'),
      '#default_value' => $config->get('contact_form.consent_after'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $config = $this->config('cassiopeia.settings');
    $values = $form_state->getValues();
    $previous = $config->get('contact_page') ?: [];
    $contact_page = $values['contact_page'];
    $contact_page['hero_image'] = $this->configManagedFile->persist(
      $this->configManagedFile->extractFid($contact_page['hero_image'] ?? []),
      'contact_page.hero_image',
      (int) ($previous['hero_image'] ?? 0),
    );
    $contact_page['aside_image'] = $this->configManagedFile->persist(
      $this->configManagedFile->extractFid($contact_page['aside_image'] ?? []),
      'contact_page.aside_image',
      (int) ($previous['aside_image'] ?? 0),
    );

    $qr_codes = [];
    foreach ($contact_page['qr_codes'] as $index => $item) {
      $previous_item = $previous['qr_codes'][$index] ?? [];
      $qr_codes[] = [
        'label' => $item['label'] ?? '',
        'image' => $this->configManagedFile->persist(
          $this->configManagedFile->extractFid($item['image'] ?? []),
          "contact_page.qr_codes.$index.image",
          (int) ($previous_item['image'] ?? 0),
        ),
        'logo' => $this->configManagedFile->persist(
          $this->configManagedFile->extractFid($item['logo'] ?? []),
          "contact_page.qr_codes.$index.logo",
          (int) ($previous_item['logo'] ?? 0),
        ),
      ];
    }
    $contact_page['qr_codes'] = $qr_codes;

    $this->config('cassiopeia.settings')
      ->set('contact_strip', $values['contact_strip'])
      ->set('cta_banner', $values['cta_banner'])
      ->set('contact_page', $contact_page)
      ->set('contact_form', $values['contact_form'])
      ->save();
  }

  /**
   * Builds a managed_file element with upload-time persistence.
   */
  protected function managedFileElement(array $element, string $usage_key, int $fid): array {
    $element['#default_value'] = $this->fileDefaultValue($fid);
    $element['#cassiopeia_file_usage_key'] = $usage_key;
    $element['#after_build'][] = [$this, 'attachManagedFileUploadPersist'];
    return $element;
  }

  /**
   * Registers a submit handler to persist files immediately after AJAX upload.
   */
  public function attachManagedFileUploadPersist(array $element, FormStateInterface $form_state): array {
    if (isset($element['upload_button'])) {
      $element['upload_button']['#submit'][] = [static::class, 'submitPersistUploadedManagedFile'];
    }
    return $element;
  }

  /**
   * Marks uploaded files permanent as soon as the Upload button is used.
   */
  public static function submitPersistUploadedManagedFile(array $form, FormStateInterface $form_state): void {
    $triggering = $form_state->getTriggeringElement();
    $parents = $triggering['#array_parents'];
    array_pop($parents);
    $element = NestedArray::getValue($form, $parents);
    $usage_key = $element['#cassiopeia_file_usage_key'] ?? '';
    if ($usage_key === '') {
      return;
    }

    $value = NestedArray::getValue($form_state->getValues(), $element['#parents']);
    /** @var \Drupal\cassiopeia\Service\CassiopeiaConfigManagedFile $helper */
    $helper = \Drupal::service('cassiopeia.config_managed_file');
    $fid = $helper->extractFid($value);
    if ($fid > 0) {
      $helper->persist($fid, $usage_key);
    }
  }

  /**
   * Builds managed_file default value from a file ID.
   */
  protected function fileDefaultValue(int $fid): array {
    return $fid > 0 ? [$fid] : [];
  }

}
