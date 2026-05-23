<?php

namespace Drupal\cassiopeia\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;

/**
 * Builds cacheable image_style render arrays.
 */
class CassiopeiaImageBuilder {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Builds an image_style render array.
   *
   * @param string $style
   *   Image style machine name.
   * @param int|string|null $fid
   *   File ID.
   * @param string|false $default
   *   Fallback URI when no file is provided.
   * @param array $attributes
   *   HTML attributes for the image tag.
   *
   * @return array|null
   *   Render array or NULL when no URI is available.
   */
  public function buildRenderArray(string $style, int|string|null $fid, string|false $default = FALSE, array $attributes = []): ?array {
    $style_name = $this->resolveStyleName($style);
    $uri = NULL;

    if (!empty($fid)) {
      $file = File::load($fid);
      if ($file) {
        $uri = $file->getFileUri();
      }
    }
    elseif ($default) {
      $uri = $default;
    }

    if ($uri === NULL) {
      return NULL;
    }

    return [
      '#theme' => 'image_style',
      '#style_name' => $style_name,
      '#uri' => $uri,
      '#attributes' => $attributes,
    ];
  }

  /**
   * Resolves a valid image style machine name.
   */
  public function resolveStyleName(string $style, string $fallback = 'medium'): string {
    if ($style === '') {
      return $fallback;
    }
    if ($this->entityTypeManager->getStorage('image_style')->load($style)) {
      return $style;
    }
    return $fallback;
  }

}
