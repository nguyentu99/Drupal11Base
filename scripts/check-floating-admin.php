<?php

use Symfony\Component\HttpFoundation\Request;

\Drupal::service('theme.initialization')->initTheme('cassiopeia_theme');
$account = \Drupal\user\Entity\User::load(1);
\Drupal::currentUser()->setAccount($account);

$kernel = \Drupal::service('http_kernel');
$response = $kernel->handle(Request::create('/'));
$html = $response->getContent();

echo 'permission: ' . ($account->hasPermission('cassiopeia admin content manager') ? 'yes' : 'no') . PHP_EOL;
echo 'floating-link in html: ' . (str_contains($html, 'cassiopeia-admin-floating-link') ? 'yes' : 'no') . PHP_EOL;
if (preg_match('/cassiopeia-admin-floating-link.*?<a[^>]+href="([^"]+)"/s', $html, $m)) {
  echo 'link href: ' . $m[1] . PHP_EOL;
}
if (preg_match('/cassiopeia-admin-floating-link.*?<a[^>]*>(.*?)<\/a>/s', $html, $m)) {
  echo 'link text: ' . strip_tags($m[1]) . PHP_EOL;
}

$kernel->terminate(Request::create('/'), $response);
