<?php

namespace Drupal\cassiopeia\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Markup as TwigMarkup;
use Drupal\Core\Render\Markup;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * CassiopeiaTwigExtension provides function and filter.
 */
class CassiopeiaTwigExtension extends AbstractExtension {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *
   */
//  protected $entityTypeManager;

  /**
   * Constructs a CassiopeiaTwigExtension
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *  The entity type manager.
   */
//  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
//    $this->entityTypeManager = $entity_type_manager;
//  }

  /**
   * Declare your custom twig function here.
   *
   * @return \Twig\TwigFunction[]
   *   TwigFunction array.
   */
  public function getFunctions() {
    return [
      new TwigFunction('cassiopeia_user_load',
        [$this, 'cassiopeia_user_load']
      ),
      new TwigFunction('cassiopeia_node_load',
        [$this, 'cassiopeia_node_load']
      ),
      new TwigFunction('cassiopeia_term_load',
        [$this, 'cassiopeia_term_load']
      ),
      new TwigFunction('cassiopeia_file_load',
        [$this, 'cassiopeia_file_load']
      ),
      new TwigFunction('cassiopeia_render_template',
        [$this, 'cassiopeia_render_template']
      ),
      new TwigFunction('cassiopeia_image_style',
        [$this, 'cassiopeia_image_style']
      ),
      new TwigFunction('cassiopeia_module_exists',
        [$this, 'cassiopeia_module_exists']
      ),
      new TwigFunction('cassiopeia_breadcrumb',
        [$this, 'cassiopeia_breadcrumb']
      ),
      new TwigFunction('cassiopeia_link',
        [$this, 'cassiopeia_link']
      ),
      new TwigFunction('cassiopeia_url',
        [$this, 'cassiopeia_url']
      ),
      new TwigFunction('cassiopeia_form',
        [$this, 'cassiopeia_form']
      ),
      new TwigFunction('drupal_form',
        [$this, 'drupal_form']
      ),
      new TwigFunction('cassiopeia_menu',
        [$this, 'cassiopeia_menu']
      ),
    ];
  }

  /**
   * Declare your custom twig filter here
   *
   * @return \Twig\TwigFilter[]
   *   TwigFilter array.
   */
  public function getFilters() {
    return [
      //      new TwigFilter(
      //        'cuttomFilter',
      //        [$this, 'cuttomFilter']
      //      ),
    ];
  }

  /**
   * @param integer $uid
   *
   */
  public function cassiopeia_user_load($uid) {
    $user = \Drupal\user\Entity\User::load($uid);
    return $user;
  }

  public function cassiopeia_node_load($nid) {
    $node = \Drupal\node\Entity\Node::load($nid);
    return $node;
  }

  public function cassiopeia_term_load($tid) {
    $term = \Drupal\taxonomy\Entity\Term::load($tid);
    return $term;
  }

  public function cassiopeia_file_load($fid) {
    $file = \Drupal\file\Entity\File::load($fid);
    return $file;
  }

  public function cassiopeia_module_exists($module) {
    $exists = FALSE;
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists($module)) {
      $exists = TRUE;
    }
    return $exists;
  }

  public function cassiopeia_render_template(string $type, string $name, string $path, mixed $variables = NULL) {
    return \Drupal::service('cassiopeia.CassiopeiaRenderTemplate')
      ->render($type, $name, $path, $variables = NULL);
  }

  public function cassiopeia_image_style($style, $fid, $default = FALSE, $attributes = []) {
    $image = NULL;
    if (!empty($fid)) {
      $file = \Drupal\file\Entity\File::load($fid);
    }
    else {
      $file = NULL;
    }

    $styles = \Drupal\image\Entity\ImageStyle::loadMultiple();
    $style_name = 'medium';
    if (array_key_exists($style, $styles)) {
      $style_name = $style;
    }

    if ($file) {
      $image_array = [
        '#theme' => 'image_style',
        '#style_name' => $style_name,
        '#uri' => $file->getFileUri(),
        '#attributes' => $attributes,
      ];
      $image = \Drupal::service('renderer')->render($image_array);
    }
    elseif ($default) {
      $image_array = [
        '#theme' => 'image_style',
        '#style_name' => $style_name,
        '#uri' => $default,
        '#attributes' => $attributes,
      ];
      $image = \Drupal::service('renderer')->render($image_array);
    }
    return $image;
  }

  public function cassiopeia_breadcrumb() {
    return \Drupal::service('breadcrumb')
      ->build(\Drupal::routeMatch())
      ->toRenderable();
  }

  public static function cassiopeia_url(string $user_input, array $options = [], bool $check_access = FALSE): ?Url {
    if (isset($options['langcode'])) {
      $language_manager = \Drupal::languageManager();
      if ($language = $language_manager->getLanguage($options['langcode'])) {
        $options['language'] = $language;
      }
    }
    if (UrlHelper::isExternal($user_input)) {
      return Url::fromUri($user_input, $options);
    }
    if (!in_array($user_input[0], ['/', '#', '?'])) {
      $user_input = '/' . $user_input;
    }
    $url = Url::fromUserInput($user_input, $options);
    return (!$check_access || $url->access()) ? $url : NULL;
  }

  public static function cassiopeia_link($text, string $user_input, array $options = [], bool $check_access = FALSE): ?Link {
    $url = self::cassiopeia_url($user_input, $options, $check_access);
    if ($url) {
      if ($text instanceof TwigMarkup) {
        $text = Markup::create($text);
      }
      return Link::fromTextAndUrl($text, $url);
    }
    return NULL;
  }

  public static function cassiopeia_form(string $form_id, ...$args): array {
    $callback = [\Drupal::formBuilder(), 'getForm'];
    return call_user_func_array($callback, func_get_args());
  }

  public static function cassiopeia_menu(string $menu_name, int $level = 1, int $depth = 0, bool $expand = FALSE): ?array {
    $menu_tree = \Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth($level);
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $menu_tree->maxDepth()));
    }
    if ($expand) {
      $parameters->expandedParents = [];
    }
    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
    $build = $menu_tree->build($tree);
    return $build;
  }
  //  public static function cuttomFilter(string $string) {
  //
  //  }

}
