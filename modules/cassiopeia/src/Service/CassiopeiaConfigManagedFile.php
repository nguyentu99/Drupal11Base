<?php

declare(strict_types=1);

namespace Drupal\cassiopeia\Service;

use Drupal\file\Entity\File;
use Drupal\file\FileUsage\FileUsageInterface;

/**
 * Persists config-managed uploads as permanent files with usage tracking.
 */
class CassiopeiaConfigManagedFile {

  private const MODULE = 'cassiopeia';

  private const TYPE = 'cassiopeia.settings';

  public function __construct(
    private readonly FileUsageInterface $fileUsage,
  ) {}

  /**
   * Extracts a file ID from a managed_file form value.
   */
  public function extractFid(mixed $value): int {
    if (!is_array($value)) {
      return (int) $value;
    }
    if (isset($value['fids']) && is_array($value['fids'])) {
      $value = $value['fids'];
    }
    $fid = reset($value);
    return $fid ? (int) $fid : 0;
  }

  /**
   * Marks a file permanent, registers usage, and cleans up replaced files.
   */
  public function persist(int $fid, string $usage_key, int $previous_fid = 0): int {
    if ($previous_fid > 0 && $previous_fid !== $fid) {
      $this->removeUsage($previous_fid, $usage_key);
    }

    if ($fid <= 0) {
      return 0;
    }

    $file = File::load($fid);
    if (!$file) {
      return 0;
    }

    $references = $this->fileUsage->listUsage($file);
    if (!isset($references[self::MODULE][self::TYPE][$usage_key])) {
      $this->fileUsage->add($file, self::MODULE, self::TYPE, $usage_key);
    }
    elseif (!$file->isPermanent()) {
      $file->setPermanent();
      $file->save();
    }

    return $fid;
  }

  /**
   * Registers usage for every contact page image referenced in config.
   *
   * @return int
   *   Number of files updated.
   */
  public function repairContactPageFiles(): int {
    $contact_page = \Drupal::config('cassiopeia.settings')->get('contact_page') ?: [];
    $count = 0;

    $map = [
      'contact_page.hero_image' => (int) ($contact_page['hero_image'] ?? 0),
      'contact_page.aside_image' => (int) ($contact_page['aside_image'] ?? 0),
    ];
    foreach ($contact_page['qr_codes'] ?? [] as $index => $item) {
      $map["contact_page.qr_codes.$index.image"] = (int) ($item['image'] ?? 0);
      $map["contact_page.qr_codes.$index.logo"] = (int) ($item['logo'] ?? 0);
    }

    foreach ($map as $usage_key => $fid) {
      if ($fid > 0 && $this->persist($fid, $usage_key) > 0) {
        $count++;
      }
    }

    return $count;
  }

  /**
   * Removes a file usage entry for a config field.
   */
  public function removeUsage(int $fid, string $usage_key): void {
    $file = File::load($fid);
    if ($file) {
      $this->fileUsage->delete($file, self::MODULE, self::TYPE, $usage_key);
    }
  }

}
