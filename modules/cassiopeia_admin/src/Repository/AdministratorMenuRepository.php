<?php

declare(strict_types=1);

namespace Drupal\cassiopeia_admin\Repository;

use Drupal\cassiopeia_admin\Entity\AdministratorBlock;
use Drupal\cassiopeia_admin\Entity\AdministratorBlockItem;
use Drupal\cassiopeia_admin\Service\AdministratorLinkMetadata;
use Drupal\cassiopeia_admin\Service\CassiopeiaAdminAdministrator;
use Drupal\cassiopeia_admin\Utility\AdministratorRecordMapper;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Data access for administrator menu blocks and items (config entities).
 */
class AdministratorMenuRepository {

  /**
   * Per-request cache of joined menu rows.
   *
   * @var object[]|null
   */
  protected ?array $menuRowsCache = NULL;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Clears the per-request menu row cache.
   */
  public function resetRequestCache(): void {
    $this->menuRowsCache = NULL;
  }

  /**
   * Loads all blocks sorted by position.
   *
   * @return object[]
   */
  public function loadAllBlocks(): array {
    $storage = $this->blockStorage();
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->sort('position')
      ->execute();
    $records = [];
    foreach ($storage->loadMultiple($ids) as $entity) {
      $records[] = AdministratorRecordMapper::blockToRecord($entity);
    }
    return $records;
  }

  /**
   * Loads items for a block sorted by position.
   *
   * @return object[]
   */
  public function loadItemsByBlock(int|string $block_id): array {
    $storage = $this->itemStorage();
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('block_id', (string) $block_id)
      ->sort('position')
      ->execute();
    $records = [];
    foreach ($storage->loadMultiple($ids) as $entity) {
      $records[] = AdministratorRecordMapper::itemToRecord($entity);
    }
    return $records;
  }

  /**
   * Loads all menu block/item rows for sidebar rendering.
   *
   * @return object[]
   *   Joined block and item records (blocks without items are omitted).
   */
  public function loadMenuTreeRows(): array {
    if ($this->menuRowsCache !== NULL) {
      return $this->menuRowsCache;
    }

    $rows = [];
    $block_storage = $this->blockStorage();
    $item_storage = $this->itemStorage();
    $block_ids = $block_storage->getQuery()
      ->accessCheck(FALSE)
      ->sort('position')
      ->execute();
    $blocks = $block_storage->loadMultiple($block_ids);

    foreach ($blocks as $block) {
      $item_ids = $item_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('block_id', $block->id())
        ->sort('position')
        ->execute();
      $items = $item_storage->loadMultiple($item_ids);
      foreach ($items as $item) {
        $rows[] = AdministratorRecordMapper::menuRowFromEntities($block, $item);
      }
    }

    $this->menuRowsCache = $rows;
    return $this->menuRowsCache;
  }

  /**
   * Loads a block by ID.
   */
  public function loadBlock(int|string $id): ?object {
    try {
      $entity = $this->blockStorage()->load((string) $id);
      return $entity ? AdministratorRecordMapper::blockToRecord($entity) : NULL;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to load administrator block @id: @message', [
        '@id' => $id,
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

  /**
   * Loads a block item by ID.
   */
  public function loadItem(int|string $id): ?object {
    try {
      $entity = $this->itemStorage()->load((string) $id);
      return $entity ? AdministratorRecordMapper::itemToRecord($entity) : NULL;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to load administrator block item @id: @message', [
        '@id' => $id,
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

  /**
   * Saves a block (insert or update).
   */
  public function saveBlock(object $block): void {
    try {
      $storage = $this->blockStorage();
      if (empty($block->id)) {
        $id = $this->nextNumericId($storage);
        $entity = $storage->create([
          'id' => $id,
          'name' => $block->name ?? '',
          'icon' => $block->icon ?? '',
          'position' => (float) ($block->position ?? 0),
        ]);
        $entity->save();
        $block->id = $entity->id();
      }
      else {
        $entity = $storage->load((string) $block->id);
        if (!$entity) {
          return;
        }
        $entity->set('name', $block->name ?? $entity->label());
        $entity->setIcon((string) ($block->icon ?? ''));
        $entity->setPosition($block->position ?? 0);
        $entity->save();
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to save administrator block: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
    $this->invalidateMenuCache();
  }

  /**
   * Updates block sort position.
   */
  public function updateBlockPosition(int|string $id, float|int|string $position): void {
    try {
      $entity = $this->blockStorage()->load((string) $id);
      if ($entity) {
        $entity->setPosition($position);
        $entity->save();
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to update administrator block position @id: @message', [
        '@id' => $id,
        '@message' => $e->getMessage(),
      ]);
    }
    $this->invalidateMenuCache();
  }

  /**
   * Deletes a block and its items.
   */
  public function deleteBlock(int|string $id): void {
    try {
      $item_storage = $this->itemStorage();
      $item_ids = $item_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('block_id', (string) $id)
        ->execute();
      $items = $item_storage->loadMultiple($item_ids);
      $item_storage->delete($items);

      $block = $this->blockStorage()->load((string) $id);
      if ($block) {
        $this->blockStorage()->delete([$block]);
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to delete administrator block @id: @message', [
        '@id' => $id,
        '@message' => $e->getMessage(),
      ]);
    }
    $this->invalidateMenuCache();
  }

  /**
   * Saves a block item (insert or update).
   */
  public function saveItem(object $item): void {
    try {
      $storage = $this->itemStorage();
      if (!empty($item->id)) {
        $existing = $storage->load((string) $item->id);
        if ($existing === NULL) {
          return;
        }
        if (isset($item->bid) && (string) $existing->getBlockId() !== (string) $item->bid) {
          $this->logger->warning('Rejected administrator block item save: block_id mismatch for item @id', [
            '@id' => $item->id,
          ]);
          return;
        }
        $item->bid = $existing->getBlockId();
      }
      if (!empty($item->link) && empty($item->url_meta)) {
        $item->url_meta = AdministratorLinkMetadata::encodeStorage($item->link);
      }
      if (empty($item->id)) {
        $id = $this->nextNumericId($storage);
        $entity = $storage->create([
          'id' => $id,
          'block_id' => (string) ($item->bid ?? ''),
          'name' => $item->name ?? '',
          'link' => $item->link ?? '',
          'icon' => $item->icon ?? '',
          'position' => (float) ($item->position ?? 0),
          'url_meta' => $item->url_meta ?? NULL,
        ]);
        $entity->save();
        $item->id = $entity->id();
      }
      else {
        $entity = $storage->load((string) $item->id);
        if (!$entity) {
          return;
        }
        $entity->set('name', $item->name ?? $entity->label());
        $entity->setLink((string) ($item->link ?? ''));
        $entity->setIcon((string) ($item->icon ?? ''));
        $entity->setPosition($item->position ?? 0);
        $entity->setUrlMeta($item->url_meta ?? NULL);
        $entity->save();
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to save administrator block item: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
    $this->invalidateMenuCache();
  }

  /**
   * Deletes a block item.
   */
  public function deleteItem(int|string $id, int|string|null $expected_bid = NULL): void {
    try {
      if ($expected_bid !== NULL && !$this->itemBelongsToBlock($id, $expected_bid)) {
        $this->logger->warning('Rejected administrator block item delete: block_id mismatch for item @id', [
          '@id' => $id,
        ]);
        return;
      }
      $entity = $this->itemStorage()->load((string) $id);
      if ($entity) {
        $this->itemStorage()->delete([$entity]);
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to delete administrator block item @id: @message', [
        '@id' => $id,
        '@message' => $e->getMessage(),
      ]);
    }
    $this->invalidateMenuCache();
  }

  /**
   * Updates block item sort position.
   */
  public function updateItemPosition(int|string $id, float|int|string $position, int|string|null $expected_bid = NULL): void {
    try {
      if ($expected_bid !== NULL && !$this->itemBelongsToBlock($id, $expected_bid)) {
        $this->logger->warning('Rejected administrator block item position update: block_id mismatch for item @id', [
          '@id' => $id,
        ]);
        return;
      }
      $entity = $this->itemStorage()->load((string) $id);
      if ($entity) {
        $entity->setPosition($position);
        $entity->save();
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to update administrator block item position @id: @message', [
        '@id' => $id,
        '@message' => $e->getMessage(),
      ]);
    }
    $this->invalidateMenuCache();
  }

  /**
   * Whether a block item belongs to the given block ID.
   */
  public function itemBelongsToBlock(int|string $item_id, int|string $block_id): bool {
    $item = $this->loadItem($item_id);
    return $item !== NULL && (string) $item->bid === (string) $block_id;
  }

  /**
   * Invalidates menu render cache and request-level row cache.
   */
  public function invalidateMenuCache(): void {
    $this->resetRequestCache();
    $this->cacheTagsInvalidator->invalidateTags([
      CassiopeiaAdminAdministrator::MENU_CACHE_TAG,
    ]);
  }

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface<\Drupal\cassiopeia_admin\Entity\AdministratorBlock>
   */
  protected function blockStorage() {
    return $this->entityTypeManager->getStorage('administrator_block');
  }

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface<\Drupal\cassiopeia_admin\Entity\AdministratorBlockItem>
   */
  protected function itemStorage() {
    return $this->entityTypeManager->getStorage('administrator_block_item');
  }

  /**
   * Generates the next numeric string ID for a storage.
   */
  protected function nextNumericId($storage): string {
    $ids = $storage->getQuery()->accessCheck(FALSE)->execute();
    $max = 0;
    foreach ($ids as $id) {
      if (is_numeric($id)) {
        $max = max($max, (int) $id);
      }
    }
    return (string) ($max + 1);
  }

}
