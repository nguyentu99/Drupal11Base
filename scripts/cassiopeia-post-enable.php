<?php

/**
 * @file
 * Sync admin_theme paths after enabling the Cassiopeia stack.
 *
 * Usage: drush php:script scripts/cassiopeia-post-enable.php
 */

$paths = implode("\n", [
  'admin',
  'admin/*',
  'user',
  'user/*',
]);

if (!\Drupal::state()->get('admin_theme_path')) {
  \Drupal::state()->set('admin_theme_path', $paths);
}

cassiopeia_admin_sync_admin_theme_paths();

print "Cassiopeia post-enable: admin_theme paths synced.\n";
