<?php

declare(strict_types=1);

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require __DIR__ . '/../autoload.php';
$request = Request::create('http://d11base.local.com/');
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$kernel->preHandle($request);

foreach (['investment_handbook', 'gallery'] as $bundle) {
  echo "=== $bundle ===\n";
  $defs = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $bundle);
  foreach ($defs as $name => $def) {
    if (str_starts_with($name, 'field_') || in_array($name, ['body', 'title'], TRUE)) {
      echo $name . ' (' . $def->getType() . ")\n";
    }
  }
}

$nids = \Drupal::entityQuery('node')->accessCheck(FALSE)->condition('type', 'investment_handbook')->range(0, 2)->execute();
echo 'nids: ' . implode(',', $nids) . "\n";
