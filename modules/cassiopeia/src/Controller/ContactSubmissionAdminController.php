<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Link;
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
      $rows[] = [
        $item['id'],
        Html::escape($item['name']),
        Html::escape($item['email']),
        Html::escape($item['phone']),
        Html::escape($item['status']),
        $this->dateFormatter->format((int) $item['created'], 'short'),
        [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'view' => [
                'title' => $this->t('View'),
                'url' => Url::fromRoute('cassiopeia.contact_submission_view', ['id' => $item['id']]),
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
      'filters' => [
        '#markup' => implode(' | ', [
          Link::fromTextAndUrl($this->t('All'), Url::fromRoute('cassiopeia.contact_submissions'))->toString(),
          Link::fromTextAndUrl($this->t('New'), Url::fromRoute('cassiopeia.contact_submissions', [], ['query' => ['status' => 'new']]))->toString(),
          Link::fromTextAndUrl($this->t('Read'), Url::fromRoute('cassiopeia.contact_submissions', [], ['query' => ['status' => 'read']]))->toString(),
        ]),
      ],
      'table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No contact submissions found.'),
      ],
    ];

    if ($total > $per_page) {
      $build['pager'] = [
        '#type' => 'pager',
        '#parameters' => $status !== '' ? ['status' => $status] : [],
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

    return [
      'back' => Link::fromTextAndUrl('← ' . $this->t('Back to list'), Url::fromRoute('cassiopeia.contact_submissions'))->toRenderable(),
      'table' => [
        '#type' => 'table',
        '#rows' => [
          [$this->t('Name'), $submission['name']],
          [$this->t('Email'), $submission['email']],
          [$this->t('Phone'), $submission['phone']],
          [$this->t('Message'), $submission['message'] !== '' ? $submission['message'] : $this->t('(empty)')],
          [$this->t('Consent'), $submission['consent'] ? $this->t('Yes') : $this->t('No')],
          [$this->t('Status'), $submission['status']],
          [$this->t('Language'), $submission['langcode']],
          [$this->t('Submitted'), $this->dateFormatter->format((int) $submission['created'], 'long')],
        ],
      ],
      'delete' => Link::createFromRoute($this->t('Delete submission'), 'cassiopeia.contact_submission_delete', ['id' => $id])->toRenderable(),
    ];
  }

}
