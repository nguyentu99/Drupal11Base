<?php

namespace Drupal\cassiopeia_admin\Storage;

use Drupal\Core\Database\Connection;

/**
 * Database storage for administrator blocks and menu items.
 */
class AdministratorStorage {

  public function __construct(
    protected Connection $database,
  ) {}

  /**
   * Loads a block by ID.
   */
  public function loadBlock(int|string $id): ?object {
    $record = $this->database->select('administrator_blocks', 'b')
      ->fields('b', ['id', 'name', 'icon', 'position'])
      ->condition('b.id', $id)
      ->execute()
      ->fetchObject();
    return $record ?: NULL;
  }

  /**
   * Saves a block (insert or update).
   */
  public function saveBlock(object|array $block): void {
    if (is_array($block)) {
      $block = (object) $block;
    }
    $fields = (array) clone $block;
    unset($fields['id']);

    if (empty($block->id)) {
      $this->database->insert('administrator_blocks')
        ->fields($fields)
        ->execute();
    }
    else {
      $this->database->update('administrator_blocks')
        ->fields($fields)
        ->condition('id', $block->id)
        ->execute();
    }
  }

  /**
   * Updates block sort position.
   */
  public function updateBlockPosition(int|string $id, $position): void {
    $this->database->update('administrator_blocks')
      ->fields(['position' => $position])
      ->condition('id', $id)
      ->execute();
  }

  /**
   * Deletes a block and its items.
   */
  public function deleteBlock(int|string $id): void {
    if (!is_numeric($id)) {
      return;
    }
    $this->database->delete('administrator_block_items')
      ->condition('bid', $id)
      ->execute();
    $this->database->delete('administrator_blocks')
      ->condition('id', $id)
      ->execute();
  }

  /**
   * Loads a block item by ID.
   */
  public function loadBlockItem(int|string $id): ?object {
    $record = $this->database->select('administrator_block_items', 'i')
      ->fields('i')
      ->condition('i.id', $id)
      ->execute()
      ->fetchObject();
    return $record ?: NULL;
  }

  /**
   * Saves a block item (insert or update).
   */
  public function saveBlockItem(object|array $item): void {
    if (is_array($item)) {
      $item = (object) $item;
    }
    $fields = (array) clone $item;
    unset($fields['id']);

    if (empty($item->id)) {
      $this->database->insert('administrator_block_items')
        ->fields($fields)
        ->execute();
    }
    else {
      $this->database->update('administrator_block_items')
        ->fields($fields)
        ->condition('id', $item->id)
        ->execute();
    }
  }

  /**
   * Updates block item sort position.
   */
  public function updateBlockItemPosition(int|string $id, $position): void {
    $this->database->update('administrator_block_items')
      ->fields(['position' => $position])
      ->condition('id', $id)
      ->execute();
  }

  /**
   * Deletes a single block item.
   */
  public function deleteBlockItem(int|string $id): void {
    if (!is_numeric($id)) {
      return;
    }
    $this->database->delete('administrator_block_items')
      ->condition('id', $id)
      ->execute();
  }

  /**
   * Returns blocks with items for menu building.
   */
  public function loadBlocksWithItems(): array {
    $query = $this->database->select('administrator_blocks', 'b');
    $query->join('administrator_block_items', 'i', 'b.id = i.bid');
    $query->addField('b', 'id', 'id');
    $query->addField('b', 'name', 'name');
    $query->addField('b', 'position', 'position');
    $query->addField('b', 'icon', 'icon');
    $query->addField('i', 'id', 'item_id');
    $query->addField('i', 'name', 'item_name');
    $query->addField('i', 'link', 'link');
    $query->addField('i', 'icon', 'item_icon');
    $query->addField('i', 'position', 'item_position');
    $query->orderBy('b.position');
    $query->orderBy('i.position');
    return $query->execute()->fetchAll();
  }

  /**
   * Loads block items for a given block ID.
   */
  public function loadBlockItemsByBlock(int|string $bid): array {
    return $this->database->select('administrator_block_items', 'i')
      ->fields('i', ['id', 'name', 'link', 'icon', 'position'])
      ->condition('bid', $bid)
      ->orderBy('position', 'ASC')
      ->execute()
      ->fetchAll();
  }

}
