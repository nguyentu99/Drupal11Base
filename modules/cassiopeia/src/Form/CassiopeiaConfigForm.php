<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Site-wide Cassiopeia page configuration.
 */
class CassiopeiaConfigForm extends ConfigFormBase {

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
    $form['contact_page']['hero_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Hero image'),
      '#upload_location' => 'public://cassiopeia/',
      '#default_value' => $this->fileDefaultValue((int) $config->get('contact_page.hero_image')),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg webp'],
      ],
    ];
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
    $form['contact_page']['aside_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Aside background image'),
      '#upload_location' => 'public://cassiopeia/',
      '#default_value' => $this->fileDefaultValue((int) $config->get('contact_page.aside_image')),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg webp'],
      ],
    ];
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
      $form['contact_page']['qr_codes'][$i]['image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('QR image'),
        '#upload_location' => 'public://cassiopeia/',
        '#default_value' => $this->fileDefaultValue((int) ($item['image'] ?? 0)),
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg webp'],
        ],
      ];
      $form['contact_page']['qr_codes'][$i]['logo'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Optional logo overlay'),
        '#upload_location' => 'public://cassiopeia/',
        '#default_value' => $this->fileDefaultValue((int) ($item['logo'] ?? 0)),
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg webp svg'],
        ],
      ];
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

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();
    $contact_page = $values['contact_page'];
    $contact_page['hero_image'] = $this->saveManagedFile($contact_page['hero_image'] ?? []);
    $contact_page['aside_image'] = $this->saveManagedFile($contact_page['aside_image'] ?? []);

    $qr_codes = [];
    foreach ($contact_page['qr_codes'] as $item) {
      $qr_codes[] = [
        'label' => $item['label'] ?? '',
        'image' => $this->saveManagedFile($item['image'] ?? []),
        'logo' => $this->saveManagedFile($item['logo'] ?? []),
      ];
    }
    $contact_page['qr_codes'] = $qr_codes;

    $this->config('cassiopeia.settings')
      ->set('contact_strip', $values['contact_strip'])
      ->set('cta_banner', $values['cta_banner'])
      ->set('contact_page', $contact_page)
      ->save();
  }

  /**
   * Builds managed_file default value from a file ID.
   */
  protected function fileDefaultValue(int $fid): array {
    return $fid > 0 ? [$fid] : [];
  }

  /**
   * Persists an uploaded file and returns its ID.
   */
  protected function saveManagedFile(array $value): int {
    $fid = (int) ($value[0] ?? 0);
    if ($fid <= 0) {
      return 0;
    }
    $file = File::load($fid);
    if ($file) {
      $file->setPermanent();
      $file->save();
    }
    return $fid;
  }

}
