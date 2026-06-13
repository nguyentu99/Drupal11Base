<?php

use Symfony\Component\HttpFoundation\Request;

$terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple();
$term = reset($terms);
if (!$term) {
  echo "no terms\n";
  return;
}

$url = $term->toUrl()->toString();
echo "term url: $url\n";

\Drupal::service('theme.initialization')->initTheme('cassiopeia_theme');
$account = \Drupal\user\Entity\User::load(1);
\Drupal::currentUser()->setAccount($account);

$kernel = \Drupal::service('http_kernel');
$response = $kernel->handle(Request::create($url));
$html = $response->getContent();

echo 'quick-actions: ' . (str_contains($html, 'cassiopeia-admin-quick-actions') ? 'yes' : 'no') . PHP_EOL;
if (preg_match_all('/cassiopeia-admin-quick-actions__item[^>]*>.*?<span>(.*?)<\/span>/s', $html, $matches)) {
  echo 'actions: ' . implode(', ', array_map('strip_tags', $matches[1])) . PHP_EOL;
}

$kernel->terminate(Request::create($url), $response);
