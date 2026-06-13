<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\node\NodeInterface;

/**
 * Seeds projects node banner and representative images from theme/HTML assets.
 */
class ProjectsContentImporter {

  /**
   * Maps project path aliases to banner and card images.
   *
   * @var array<string, array{banner: string, image: string}>
   */
  private const ALIAS_IMAGE_MAP = [
    'kcn-nam-binh-xuyen-green-park' => [
      'banner' => 'projects/green-park/hero.jpg',
      'image' => 'services/featured-projects/kcn-nam-binh-xuyen-green-park.jpg',
    ],
    'cnctech-ba-thien-1' => [
      'banner' => 'ba-thien-1-banner.jpg',
      'image' => 'services/featured-projects/cnctech-ba-thien-1.jpg',
    ],
    'tt-logistics-bac-giang' => [
      'banner' => 'banner-bac-gian.jpg',
      'image' => 'services/featured-projects/tt-logistics-bac-giang.jpg',
    ],
    'kcn-thang-long-3' => [
      'banner' => 'thang-long-2.jpg',
      'image' => 'thang-long-2.jpg',
    ],
    'kcn-thang-long-2' => [
      'banner' => 'thang-long-2.jpg',
      'image' => 'thang-long-2.jpg',
    ],
    'khu-cong-nghiep-binh-xuyen' => [
      'banner' => 'binh-xuyen-banner.jpg',
      'image' => 'du-an-thuml.jpg',
    ],
    'cnctech-ha-nam' => [
      'banner' => 'ha-nam-banner.jpg',
      'image' => 'ha-nam-banner.jpg',
    ],
    'cum-cong-nghiep-hop-thinh' => [
      'banner' => 'hop-thinh-banner.jpg',
      'image' => 'hop-thinh-banner.jpg',
    ],
  ];

  /**
   * Title keyword fallbacks when alias is missing.
   *
   * @var array<int, array{match: string, banner: string, image: string}>
   */
  private const TITLE_IMAGE_MAP = [
    ['match' => 'Green Park', 'banner' => 'projects/green-park/hero.jpg', 'image' => 'services/featured-projects/kcn-nam-binh-xuyen-green-park.jpg'],
    ['match' => 'Bá Thiện', 'banner' => 'ba-thien-1-banner.jpg', 'image' => 'services/featured-projects/cnctech-ba-thien-1.jpg'],
    ['match' => 'TT Logistics', 'banner' => 'banner-bac-gian.jpg', 'image' => 'services/featured-projects/tt-logistics-bac-giang.jpg'],
    ['match' => 'Thăng Long 3', 'banner' => 'thang-long-2.jpg', 'image' => 'thang-long-2.jpg'],
    ['match' => 'Thăng Long 2', 'banner' => 'thang-long-2.jpg', 'image' => 'thang-long-2.jpg'],
    ['match' => 'Bình Xuyên', 'banner' => 'binh-xuyen-banner.jpg', 'image' => 'du-an-thuml.jpg'],
    ['match' => 'Hà Nam', 'banner' => 'ha-nam-banner.jpg', 'image' => 'ha-nam-banner.jpg'],
    ['match' => 'Hợp Thịnh', 'banner' => 'hop-thinh-banner.jpg', 'image' => 'hop-thinh-banner.jpg'],
  ];

  /**
   * @var array<string, int>
   */
  private array $fileCache = [];

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly FileRepositoryInterface $fileRepository,
  ) {}

  /**
   * Imports banner and representative images for all projects nodes.
   *
   * @return string[]
   */
  public function import(): array {
    $messages = [];
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'projects')
      ->execute();

    if ($nids === []) {
      $messages[] = (string) t('No projects nodes found.');
      return $messages;
    }

    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $storage->loadMultiple($nids);
    foreach ($nodes as $node) {
      $messages = array_merge($messages, $this->importNodeImages($node));
    }

    return $messages;
  }

  /**
   * @return string[]
   */
  private function importNodeImages(NodeInterface $node): array {
    $messages = [];
    $mapping = $this->resolveImageMapping($node);
    if ($mapping === NULL) {
      $messages[] = (string) t('Skipped project @title — no image mapping found.', [
        '@title' => $node->label(),
      ]);
      return $messages;
    }

    $changed = FALSE;
    $alt = $node->label();

    if ($node->hasField('field_banner')) {
      $banner = $this->imageField($mapping['banner'], $alt);
      if ($banner !== NULL) {
        $node->set('field_banner', $banner);
        $changed = TRUE;
      }
    }

    if ($node->hasField('field_image')) {
      $image = $this->imageField($mapping['image'], $alt);
      if ($image !== NULL) {
        $node->set('field_image', $image);
        $changed = TRUE;
      }
    }

    if (!$changed) {
      $messages[] = (string) t('Project @title — image files missing.', [
        '@title' => $node->label(),
      ]);
      return $messages;
    }

    $node->save();
    $messages[] = (string) t('Updated images for project @title (node @nid).', [
      '@title' => $node->label(),
      '@nid' => $node->id(),
    ]);

    return $messages;
  }

  /**
   * @return array{banner: string, image: string}|null
   */
  private function resolveImageMapping(NodeInterface $node): ?array {
    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $node->id());
    $slug = trim($alias, '/');
    if (isset(self::ALIAS_IMAGE_MAP[$slug])) {
      return self::ALIAS_IMAGE_MAP[$slug];
    }

    $title = $node->label();
    foreach (self::TITLE_IMAGE_MAP as $entry) {
      if (stripos($title, $entry['match']) !== FALSE) {
        return [
          'banner' => $entry['banner'],
          'image' => $entry['image'],
        ];
      }
    }

    return NULL;
  }

  /**
   * @return array<string, mixed>|null
   */
  private function imageField(string $relative_path, string $alt = ''): ?array {
    $fid = $this->ensureFile($relative_path);
    if ($fid === NULL) {
      return NULL;
    }
    return [
      'target_id' => $fid,
      'alt' => $alt,
    ];
  }

  private function ensureFile(string $relative_path): ?int {
    $relative_path = $this->normalizeRelativePath($relative_path);
    if (isset($this->fileCache[$relative_path])) {
      return $this->fileCache[$relative_path];
    }

    $absolute = $this->resolveAbsolutePath($relative_path);
    if ($absolute === NULL) {
      return NULL;
    }

    $directory = 'public://projects';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $destination = $directory . '/' . basename($absolute);
    $data = file_get_contents($absolute);
    if ($data === FALSE) {
      return NULL;
    }

    $existing = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $destination]);
    if ($existing !== []) {
      $file = reset($existing);
      $this->fileCache[$relative_path] = (int) $file->id();
      return $this->fileCache[$relative_path];
    }

    $file = $this->fileRepository->writeData($data, $destination, FileSystemInterface::EXISTS_REPLACE);
    $file->setPermanent();
    $file->save();
    $this->fileCache[$relative_path] = (int) $file->id();
    return $this->fileCache[$relative_path];
  }

  private function normalizeRelativePath(string $path): string {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#^(\./)+#', '', $path) ?? $path;
    if (str_starts_with($path, 'images/')) {
      $path = substr($path, 7);
    }
    return $path;
  }

  private function resolveAbsolutePath(string $relative): ?string {
    $roots = [
      DRUPAL_ROOT . '/html/images/',
      DRUPAL_ROOT . '/themes/cassiopeia_theme/images/',
    ];

    foreach ($roots as $root) {
      $absolute = $root . $relative;
      if (is_readable($absolute)) {
        return $absolute;
      }
    }

    return NULL;
  }

}
