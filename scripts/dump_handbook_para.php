<?php

declare(strict_types=1);

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require __DIR__ . '/../autoload.php';
$request = Request::create('http://d11base.local.com/');
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$kernel->preHandle($request);

$nid = \Drupal::entityQuery('node')->accessCheck(FALSE)->condition('type', 'investment_handbook')->range(0, 1)->execute();
if (!$nid) {
  echo "no nodes\n";
  exit(0);
}
$node = \Drupal::entityTypeManager()->getStorage('node')->load(reset($nid));
echo "Node: {$node->label()}\n";
if ($node->get('field_para_content')->isEmpty()) {
  echo "no paragraphs\n";
  exit(0);
}
foreach ($node->get('field_para_content') as $item) {
  $para = $item->entity;
  if (!$para) {
    continue;
  }
  echo "Paragraph bundle: {$para->bundle()}\n";
  $defs = \Drupal::service('entity_field.manager')->getFieldDefinitions('paragraph', $para->bundle());
  foreach ($defs as $name => $def) {
    if (str_starts_with($name, 'field_') || in_array($name, ['body'], TRUE)) {
      echo "  $name ({$def->getType()})\n";
      if (!$para->get($name)->isEmpty()) {
        $field = $para->get($name);
        if ($def->getType() === 'text_long' || $def->getType() === 'text_with_summary') {
          echo "    value len: " . strlen((string) $field->value) . "\n";
        }
        elseif ($def->getType() === 'string') {
          echo "    value: {$field->value}\n";
        }
      }
    }
  }
}
