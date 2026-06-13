<?php

use Symfony\Component\HttpFoundation\Request;

\Drupal::service('theme.initialization')->initTheme('cassiopeia_theme');
$account = \Drupal\user\Entity\User::load(1);
\Drupal::currentUser()->setAccount($account);

$kernel = \Drupal::service('http_kernel');
$response = $kernel->handle(Request::create('/node/14'));
$html = $response->getContent();

echo 'permission: ' . ($account->hasPermission('cassiopeia admin content manager') ? 'yes' : 'no') . PHP_EOL;
echo 'quick-actions in html: ' . (str_contains($html, 'cassiopeia-admin-quick-actions') ? 'yes' : 'no') . PHP_EOL;

if (preg_match_all('/cassiopeia-admin-quick-actions__item[^>]*>.*?<span>(.*?)<\/span>/s', $html, $matches)) {
  echo 'actions: ' . implode(', ', array_map('strip_tags', $matches[1])) . PHP_EOL;
}

$kernel->terminate(Request::create('/node/14'), $response);
