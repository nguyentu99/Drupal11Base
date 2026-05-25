<?php
/**
 * @file
 * Copy to settings.local.php and enable the include at the end of settings.php.
 *
 * Trusted host patterns for local development (d11base.local.com).
 */
$settings['trusted_host_patterns'] = [
  '^d11base\.local\.com$',
  '^www\.d11base\.local\.com$',
  '^localhost$',
  '^127\.0\.0\.1$',
];
