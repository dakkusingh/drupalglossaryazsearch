<?php

namespace Drupal\search_api_glossary\Plugin\facets\processor;

use Drupal\facets\Processor\WidgetOrderPluginBase;
use Drupal\facets\Processor\WidgetOrderProcessorInterface;
use Drupal\facets\Result\Result;

/**
 * A processor that orders the results by display value.
 *
 * @FacetsProcessor(
 *   id = "glossaryaz_widget_order",
 *   label = @Translation("Sort by Glossary AZ"),
 *   description = @Translation("Sort by Glossary AZ then 0-9 and then #."),
 *   stages = {
 *     "build" = 100
 *   }
 * )
 */
class GlossaryAZWidgetOrderProcessor extends WidgetOrderPluginBase implements WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function sortResults(array $results, $order = 'ASC') {
    // TODO figure out a custom sort instead of ASC DESC
    usort($results, 'self::sortGlossaryAZDefault');
    return $results;
  }

  /**
   * Sorts default.
   */
  protected static function sortGlossaryAZDefault(Result $a, Result $b) {

    // TODO Maybe check if the values are present.
    $a_value = $a->getDisplayValue();
    $b_value = $b->getDisplayValue();

    // TODO make some way to make 0-9 configurable.
    // so we can have seperate bins for 0,1,2,3 etc

    if ($a_value == $b_value) {
      return 0;
    }
    elseif (ctype_alpha($a_value) && ctype_alpha($b_value)) {
      return ($a_value < $b_value) ? -1 : 1;
    }
    elseif (($a_value == "#" || $a_value == "0-9") && ctype_alpha($b_value)) {
      return 1;
    }
    elseif (ctype_alpha($a_value) && ($b_value == "#" || $b_value == "0-9")) {
      return -1;
    }
    elseif ($a_value == "#" && $b_value == "0-9") {
      return 1;
    }
    elseif ($b_value == "0-9" && $a_value == "#") {
      return -1;
    }

  }

}
