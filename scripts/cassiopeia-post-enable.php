<?php

/**
 * @file
 * One-off bootstrap script: admin paths + schema version after enabling modules.
 *
 * Usage: php vendor/bin/drush php:script scripts/cassiopeia-post-enable.php
 */

$paths = implode("\n", [
  'administrator',
  'admin',
  'admin/*',
  'manager',
  'manager/*',
  'user',
  'user/*',
]);

if (!\Drupal::state()->get('admin_theme_path')) {
  \Drupal::state()->set('admin_theme_path', $paths);
}

cassiopeia_admin_sync_admin_theme_paths();
\Drupal::keyValue('system.schema')->set('cassiopeia_admin', 11004);

print "Cassiopeia post-enable: admin_theme paths synced, schema set to 11004.\n";
