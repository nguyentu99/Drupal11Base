<?php

use Symfony\Component\HttpFoundation\Request;

\Drupal::service('theme.initialization')->initTheme('cassiopeia_admin_theme');
$account = \Drupal\user\Entity\User::load(1);
\Drupal::currentUser()->setAccount($account);

$kernel = \Drupal::service('http_kernel');
$response = $kernel->handle(Request::create('/admin/dashboard'));
echo 'status: ' . $response->getStatusCode() . PHP_EOL;
$html = $response->getContent();
echo 'dashboard theme: ' . (str_contains($html, 'cassiopeia-admin-dashboard') ? 'yes' : 'no') . PHP_EOL;
echo 'small-box: ' . (str_contains($html, 'small-box') ? 'yes' : 'no') . PHP_EOL;

$kernel->terminate(Request::create('/admin/dashboard'), $response);
