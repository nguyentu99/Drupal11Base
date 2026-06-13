<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Admin UI for contact form submissions.
 */
class ContactSubmissionAdminController extends ControllerBase {

  public function __construct(
    protected DateFormatterInterface $dateFormatter,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('date.formatter'));
  }

  /**
   * Lists contact submissions.
   */
  public function listing(Request $request): array {
    $status = trim((string) $request->query->get('status', ''));
    $page = max(0, (int) $request->query->get('page', 0));
    $per_page = 25;
    $filters = ['status' => $status !== '' ? $status : NULL];

    $total = cassiopeia_contact_submission_count($filters);
    $total_all = cassiopeia_contact_submission_count([]);
    \Drupal::service('pager.manager')->createPager($total, $per_page);

    $result = cassiopeia_contact_submission_list($filters + [
      'limit' => $per_page,
      'offset' => $page * $per_page,
    ]);

    $header = [
      $this->t('ID'),
      $this->t('Name'),
      $this->t('Email'),
      $this->t('Phone'),
      $this->t('Status'),
      $this->t('Submitted'),
      $this->t('Operations'),
    ];

    $rows = [];
    foreach ($result['items'] as $item) {
      $view_url = Url::fromRoute('cassiopeia.contact_submission_view', ['id' => $item['id']]);
      $rows[] = [
        ['data' => ['#markup' => '#' . (int) $item['id']]],
        [
          'data' => Link::fromTextAndUrl(Html::escape($item['name']), $view_url)->toRenderable() + [
            '#attributes' => ['class' => ['cassiopeia-contact-submissions__name-link']],
          ],
        ],
        [
          'data' => [
            '#type' => 'link',
            '#title' => Html::escape($item['email']),
            '#url' => Url::fromUri('mailto:' . $item['email']),
            '#attributes' => ['class' => ['cassiopeia-contact-submissions__meta-link']],
          ],
        ],
        [
          'data' => $item['phone'] !== '' ? [
            '#type' => 'link',
            '#title' => Html::escape($item['phone']),
            '#url' => Url::fromUri('tel:' . preg_replace('/\D+/', '', $item['phone'])),
            '#attributes' => ['class' => ['cassiopeia-contact-submissions__meta-link']],
          ] : ['#markup' => '—'],
        ],
        ['data' => $this->statusBadge((string) $item['status'])],
        ['data' => ['#markup' => $this->dateFormatter->format((int) $item['created'], 'short')]],
        [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'view' => [
                'title' => $this->t('View'),
                'url' => $view_url,
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('cassiopeia.contact_submission_delete', ['id' => $item['id']]),
              ],
            ],
          ],
        ],
      ];
    }

    $build = [
      '#attached' => [
        'library' => ['cassiopeia/contact-submissions-admin'],
      ],
      '#attributes' => [
        'class' => ['cassiopeia-contact-submissions'],
      ],
      'toolbar' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['cassiopeia-contact-submissions__toolbar']],
        'summary' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $status === ''
            ? $this->formatPlural($total_all, '1 submission', '@count submissions')
            : $this->t('@filtered of @total submissions', [
              '@filtered' => $total,
              '@total' => $total_all,
            ]),
          '#attributes' => ['class' => ['cassiopeia-contact-submissions__summary']],
        ],
        'filters' => $this->filterLinks($status),
      ],
      'table_wrap' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['cassiopeia-contact-submissions__table-wrap']],
        'table' => [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
          '#empty' => $this->t('No contact submissions found.'),
          '#attributes' => ['class' => ['cassiopeia-contact-submissions__table']],
        ],
      ],
    ];

    if ($total > $per_page) {
      $build['pager'] = [
        '#type' => 'pager',
        '#parameters' => $status !== '' ? ['status' => $status] : [],
        '#attributes' => ['class' => ['cassiopeia-contact-submissions__pager']],
      ];
    }

    return $build;
  }

  /**
   * Displays a single submission.
   */
  public function view(int $id): array {
    $submission = cassiopeia_contact_submission_load($id);
    if (!$submission) {
      throw new NotFoundHttpException();
    }

    if ($submission['status'] === 'new') {
      cassiopeia_contact_submission_update($id, ['status' => 'read']);
      $submission['status'] = 'read';
    }

    $message = $submission['message'] !== ''
      ? [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => nl2br(Html::escape($submission['message'])),
        '#attributes' => ['class' => ['cassiopeia-contact-submission-view__message']],
      ]
      : ['#markup' => $this->t('(empty)')];

    $delete_link = Link::createFromRoute($this->t('Delete submission'), 'cassiopeia.contact_submission_delete', ['id' => $id])->toRenderable();
    $delete_link['#attributes']['class'][] = 'cassiopeia-contact-submission-view__delete';

    return [
      '#attached' => [
        'library' => ['cassiopeia/contact-submissions-admin'],
      ],
      '#attributes' => [
        'class' => ['cassiopeia-contact-submission-view'],
      ],
      'toolbar' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['cassiopeia-contact-submission-view__toolbar']],
        'back' => Link::fromTextAndUrl('← ' . $this->t('Back to list'), Url::fromRoute('cassiopeia.contact_submissions'))->toRenderable() + [
          '#attributes' => ['class' => ['cassiopeia-contact-submission-view__back']],
        ],
        'delete' => $delete_link,
      ],
      'table' => [
        '#type' => 'table',
        '#attributes' => ['class' => ['cassiopeia-contact-submission-view__detail']],
        '#rows' => [
          [$this->t('Name'), Html::escape($submission['name'])],
          [
            $this->t('Email'),
            [
              '#type' => 'link',
              '#title' => Html::escape($submission['email']),
              '#url' => Url::fromUri('mailto:' . $submission['email']),
            ],
          ],
          [
            $this->t('Phone'),
            $submission['phone'] !== '' ? [
              '#type' => 'link',
              '#title' => Html::escape($submission['phone']),
              '#url' => Url::fromUri('tel:' . preg_replace('/\D+/', '', $submission['phone'])),
            ] : ['#markup' => '—'],
          ],
          [$this->t('Message'), $message],
          [$this->t('Consent'), $submission['consent'] ? $this->t('Yes') : $this->t('No')],
          [$this->t('Status'), $this->statusBadge((string) $submission['status'])],
          [$this->t('Language'), Html::escape($submission['langcode'])],
          [$this->t('Submitted'), $this->dateFormatter->format((int) $submission['created'], 'long')],
        ],
      ],
    ];
  }

  /**
   * Builds filter pill links for the listing page.
   */
  protected function filterLinks(string $active_status): array {
    $definitions = [
      '' => $this->t('All'),
      'new' => $this->t('New'),
      'read' => $this->t('Read'),
    ];

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['cassiopeia-contact-submissions__filters']],
    ];
    foreach ($definitions as $status => $label) {
      $count = cassiopeia_contact_submission_count($status === '' ? [] : ['status' => $status]);
      $link = Link::fromTextAndUrl(
        Markup::create(Html::escape((string) $label) . '<span class="cassiopeia-contact-submissions__filter-count">' . $count . '</span>'),
        Url::fromRoute('cassiopeia.contact_submissions', [], $status !== '' ? ['query' => ['status' => $status]] : []),
      )->toRenderable();
      $link['#attributes']['class'][] = 'cassiopeia-contact-submissions__filter';
      if ($active_status === $status) {
        $link['#attributes']['class'][] = 'is-active';
      }
      $build[] = $link;
    }

    return $build;
  }

  /**
   * Builds a styled status badge render element.
   */
  protected function statusBadge(string $status): array {
    $labels = [
      'new' => $this->t('New'),
      'read' => $this->t('Read'),
    ];
    $label = $labels[$status] ?? $status;

    return [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $label,
      '#attributes' => [
        'class' => [
          'cassiopeia-contact-submissions__status',
          'cassiopeia-contact-submissions__status--' . Html::cleanCssIdentifier($status),
        ],
      ],
    ];
  }

}
