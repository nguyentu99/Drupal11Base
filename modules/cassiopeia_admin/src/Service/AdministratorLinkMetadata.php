<?php

namespace Drupal\cassiopeia_admin\Service;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;

/**
 * Encodes and decodes persisted menu link metadata for administrator items.
 */
final class AdministratorLinkMetadata {

  /**
   * Builds metadata array from a user-entered internal path or external URL.
   */
  public static function encode(string $link): ?array {
    $link = trim($link);
    if ($link === '') {
      return NULL;
    }

    if (UrlHelper::isExternal($link)) {
      return [
        'external' => TRUE,
        'uri' => $link,
      ];
    }

    $url = \Drupal::service('path.validator')->getUrlIfValid($link);
    if (!$url) {
      return NULL;
    }

    $query = $url->getOption('query');
    if (!is_array($query)) {
      $query = self::parseQueryFromLink($link);
    }

    return [
      'route_name' => $url->getRouteName(),
      'route_parameters' => $url->getRouteParameters(),
      'query' => $query,
    ];
  }

  /**
   * Encodes metadata for database storage.
   */
  public static function encodeStorage(string $link): ?string {
    $meta = self::encode($link);
    return $meta ? json_encode($meta) : NULL;
  }

  /**
   * Restores a URL object from stored metadata, with legacy fallback.
   */
  public static function toUrl(?string $url_meta, string $fallback_link): ?Url {
    if ($url_meta) {
      $meta = json_decode($url_meta, TRUE);
      if (is_array($meta)) {
        if (!empty($meta['external']) && !empty($meta['uri'])) {
          return Url::fromUri($meta['uri']);
        }
        if (!empty($meta['route_name'])) {
          $url = Url::fromRoute($meta['route_name'], $meta['route_parameters'] ?? []);
          if (!empty($meta['query']) && is_array($meta['query'])) {
            $url->setOption('query', $meta['query']);
          }
          return $url;
        }
      }
    }

    return \Drupal::service('path.validator')->getUrlIfValid($fallback_link);
  }

  /**
   * Parses query string from a path with ?foo=bar.
   */
  protected static function parseQueryFromLink(string $link): array {
    $parts = explode('?', $link, 2);
    if (count($parts) !== 2) {
      return [];
    }
    $query = [];
    foreach (explode('&', $parts[1]) as $part) {
      $pair = explode('=', $part, 2);
      if (count($pair) === 2) {
        $query[rawurldecode($pair[0])] = rawurldecode($pair[1]);
      }
    }
    return $query;
  }

}
