<?php

/**
 * @file
 * Appends settings.local.php include to sites/default/settings.php if missing.
 *
 * Usage: php scripts/patch-settings-include.php
 */

$root = dirname(__DIR__);
$settings = $root . '/sites/default/settings.php';
$include = <<<'PHP'

if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
PHP;

if (!is_file($settings)) {
  fwrite(STDERR, "settings.php not found.\n");
  exit(1);
}

$content = file_get_contents($settings);
if (preg_match('/^\s*if\s*\(\s*file_exists\s*\(\s*\$app_root\s*\./m', $content)) {
  echo "settings.local.php include already present.\n";
  exit(0);
}

if (file_put_contents($settings, $content . $include, LOCK_EX) === FALSE) {
  fwrite(STDERR, "Could not write settings.php — add the include block manually.\n");
  exit(1);
}

echo "Appended settings.local.php include to settings.php.\n";
