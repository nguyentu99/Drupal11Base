<?php

/**
 * @file
 * Clears Drupal bootstrap cache after extension path changes.
 *
 * Usage: php scripts/rebuild-extension-paths.php
 */

$root = dirname(__DIR__);
$site = $root . '/sites/default';
$app_root = $root;
$site_path = 'sites/default';

foreach (['settings.php', 'settings.local.php'] as $file) {
  $path = $site . '/' . $file;
  if (is_file($path)) {
    include $path;
  }
}

if (empty($databases['default']['default'])) {
  fwrite(STDERR, "Database settings not found.\n");
  exit(1);
}

$db = $databases['default']['default'];
$dsn = sprintf(
  'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
  $db['host'] ?? 'localhost',
  $db['port'] ?? 3306,
  $db['database'],
);

$pdo = new PDO($dsn, $db['username'], $db['password'] ?? '', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$prefix = $db['prefix'] ?? '';
foreach (['cache_bootstrap', 'cache_discovery', 'cache_config', 'cache_container'] as $bin) {
  $table = $prefix . $bin;
  try {
    $pdo->exec("TRUNCATE TABLE `$table`");
    echo "Truncated $table\n";
  }
  catch (PDOException $e) {
    echo "Skip $table\n";
  }
}

echo "Done. Run: drush cr\n";
