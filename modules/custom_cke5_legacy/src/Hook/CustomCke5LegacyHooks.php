<?php

declare(strict_types=1);

namespace Drupal\custom_cke5_legacy\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for CKEditor 5 Legacy Features.
 */
final class CustomCke5LegacyHooks {

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments): void {
    $attachments['#attached']['library'][] = 'custom_cke5_legacy/legacy_content';
  }

}
