<?php

/**
 * @file
 * Copy to settings.local.php and include at the end of settings.php:
 *
 * @code
 * if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
 *   include $app_root . '/' . $site_path . '/settings.local.php';
 * }
 * @endcode
 */

$settings['trusted_host_patterns'] = [
  '^d11base\.local\.com$',
  '^www\.d11base\.local\.com$',
  '^localhost$',
  '^127\.0\.0\.1$',
];

// Optional: CSS/JS aggregation (admin/config/development/performance).
// $config['system.performance']['css']['preprocess'] = TRUE;
// $config['system.performance']['js']['preprocess'] = TRUE;
