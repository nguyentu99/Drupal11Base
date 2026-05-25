<?php

/**
 * @file
 * Registers administrator_block config entity types with Drupal (status report fix).
 *
 * Usage: php vendor/bin/drush php:script scripts/install-administrator-entities.php
 */

$manager = \Drupal::entityDefinitionUpdateManager();
$entity_type_manager = \Drupal::entityTypeManager();

foreach (['administrator_block', 'administrator_block_item'] as $entity_type_id) {
  if ($manager->getEntityType($entity_type_id) === NULL) {
    $manager->installEntityType($entity_type_manager->getDefinition($entity_type_id));
    print "Installed entity type: {$entity_type_id}\n";
  }
  else {
    print "Entity type already installed: {$entity_type_id}\n";
  }
}

print "Done.\n";
