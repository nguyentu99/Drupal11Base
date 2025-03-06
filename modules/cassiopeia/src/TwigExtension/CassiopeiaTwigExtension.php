<?php

namespace Drupal\cassiopeia\TwigExtension;

//use Drupal\block\BlockInterface;
//use Drupal\block\Entity\Block;
//use Drupal\Core\Entity\EntityTypeManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * CassiopeiaTwigExtension provides function and filter.
 */
class CassiopeiaTwigExtension extends AbstractExtension {


  /**
   * Declare your custom twig function here.
   *
   * @return \Twig\TwigFunction[]
   *   TwigFunction array.
   */
  public function getFunctions() {
    return [
//      new TwigFunction('cuttomFunction',
//        [$this, 'cuttomFunction'],
//        ['is_safe' => ['html']]
//      ),
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

//        new TwigFilter(
//          'cuttomRender',
//          [$this, 'cuttomRender']
//        ),
    ];
  }
//  public function cuttomFunction() {

//  }

//  public function cuttomRender (string $type, string $name, string $path, mixed $variables = null) {
//    return  \Drupal::service('cassiopeia.CassiopeiaRenderTemplate')->render($type,$name,$path,$variables = null);
//  }


//  public static function cuttomFilter(string $string) {
//
//  }

}
