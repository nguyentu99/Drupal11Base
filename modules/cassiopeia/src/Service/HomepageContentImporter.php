<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\node\Entity\Node;

/**
 * Seeds homepage-related admin content from HTML/theme defaults.
 */
class HomepageContentImporter {

  /**
   * @var array<string, int>
   */
  private array $fileCache = [];

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly FileRepositoryInterface $fileRepository,
  ) {}

  /**
   * Imports homepage banner and path aliases when missing.
   *
   * @return string[]
   */
  public function import(): array {
    $messages = [];
    $messages = array_merge($messages, $this->ensureHomeBanner());
    $messages = array_merge($messages, $this->ensureProjectAliases());
    return $messages;
  }

  /**
   * @return string[]
   */
  private function ensureHomeBanner(): array {
    $messages = [];
    $existing = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'banner')
      ->condition('field_link', 'home')
      ->range(0, 1)
      ->execute();

    if ($existing !== []) {
      $messages[] = (string) t('Skipped homepage banner — already exists.');
      return $messages;
    }

    $image = $this->imageField('images/banner.jpg', 'Khu công nghiệp Green Park IP');
    if ($image === NULL) {
      $messages[] = (string) t('Homepage banner not created — banner image missing.');
      return $messages;
    }

    $node = Node::create([
      'type' => 'banner',
      'title' => 'Trang chủ',
      'status' => 1,
      'langcode' => 'vi',
      'field_link' => 'home',
      'field_image' => $image,
    ]);
    $node->save();
    $messages[] = (string) t('Created homepage banner (node @nid).', ['@nid' => $node->id()]);
    return $messages;
  }

  /**
   * @return string[]
   */
  private function ensureProjectAliases(): array {
    $messages = [];
    $aliases = [
      8 => 'kcn-nam-binh-xuyen-green-park',
    ];

    foreach ($aliases as $nid => $alias) {
      $path = '/' . $alias;
      if ($this->aliasExists($path)) {
        continue;
      }
      $node = Node::load($nid);
      if (!$node || $node->bundle() !== 'projects') {
        continue;
      }
      $node->set('path', [
        'alias' => $path,
        'pathauto' => 0,
      ]);
      $node->save();
      $messages[] = (string) t('Set path alias @alias for project node @nid.', [
        '@alias' => $path,
        '@nid' => $nid,
      ]);
    }

    return $messages;
  }

  private function aliasExists(string $alias): bool {
    $existing = \Drupal::entityTypeManager()->getStorage('path_alias')
      ->loadByProperties(['alias' => $alias, 'langcode' => 'vi']);
    return $existing !== [];
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
    $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');
    if (str_starts_with($relative_path, 'images/')) {
      $relative_path = substr($relative_path, 7);
    }

    if (isset($this->fileCache[$relative_path])) {
      return $this->fileCache[$relative_path];
    }

    $roots = [
      DRUPAL_ROOT . '/themes/cassiopeia_theme/images/',
      DRUPAL_ROOT . '/html/images/',
    ];

    $absolute = NULL;
    foreach ($roots as $root) {
      $candidate = $root . $relative_path;
      if (is_readable($candidate)) {
        $absolute = $candidate;
        break;
      }
    }

    if ($absolute === NULL) {
      return NULL;
    }

    $directory = 'public://homepage';
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

}
