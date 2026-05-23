<?php

namespace Drupal\cassiopeia_admin\ParamConverter;

use Drupal\cassiopeia_admin\Utility\AdministratorAccessHelper;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Converts administrator block item route parameters to item records.
 */
class AdministratorBlockItemParamConverter implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $item = administrator_block_item_load($value);
    if ($item === NULL) {
      return NULL;
    }
    if (isset($defaults['administrator_block']) && !AdministratorAccessHelper::itemBelongsToBlock($item, $defaults['administrator_block'])) {
      return NULL;
    }
    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] === 'administrator_block_item';
  }

}
