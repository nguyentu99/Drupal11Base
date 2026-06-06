<?php

/**
 * @file
 * Reports cache plugin settings for every Views display.
 *
 * Usage: drush php:script scripts/views-cache-audit.php
 */

if (!\Drupal::moduleHandler()->moduleExists('views')) {
  echo "Views module is not enabled.\n";
  return;
}

$views = \Drupal::entityTypeManager()->getStorage('view')->loadMultiple();
$rows = [];
foreach ($views as $view) {
  foreach ($view->get('display') as $display_id => $display) {
    if (empty($display['display_options'])) {
      continue;
    }
    $cache = $display['display_options']['cache'] ?? ['type' => 'tag', 'options' => []];
    $rows[] = [
      'view' => $view->id(),
      'display' => $display_id,
      'cache_type' => $cache['type'] ?? 'none',
      'max_age' => $cache['options']['max_age'] ?? '-',
    ];
  }
}

if ($rows === []) {
  echo "No Views displays found.\n";
  return;
}

echo str_pad('View', 22) . str_pad('Display', 14) . str_pad('Cache', 12) . "Max age\n";
echo str_repeat('-', 60) . "\n";
foreach ($rows as $row) {
  echo str_pad($row['view'], 22)
    . str_pad($row['display'], 14)
    . str_pad((string) $row['cache_type'], 12)
    . $row['max_age'] . "\n";
}
