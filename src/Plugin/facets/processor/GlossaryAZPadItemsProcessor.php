<?php

namespace Drupal\search_api_glossary\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\facets\Result\Result;

/**
 * Provides a processor to rewrite facet results to pad out missing alpha.
 *
 * @FacetsProcessor(
 *   id = "glossaryaz_pad_items_processor",
 *   label = @Translation("Add missing items to Glossary AZ"),
 *   description = @Translation("Rewrite facet results to pad out missing Glossary AZ"),
 *   stages = {
 *     "build" = 10
 *   }
 * )
 */
class GlossaryAZPadItemsProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $glossary_array = range('A', 'Z');

    // @todo make non alpha numeric expandable.
    $glossary_array[] = "#";

    // @todo make 0-9 expandable.
    $glossary_array[] = "0-9";

    // Generate keys from values.
    $glossary_missing = array_combine($glossary_array, $glossary_array);

    /** @var \Drupal\facets\Result\ResultInterface $result */
    foreach ($results as $result) {
      $result_glossary = $result->getDisplayValue();

      // Items that exist in result, remove them from sample array.
      if (in_array($result_glossary, $glossary_missing)) {
        unset($glossary_missing[$result_glossary]);
      }
    }

    // Loop over the missing items and add them.
    foreach ($glossary_missing as $glossary) {
      $results[] = new Result($glossary, $glossary, 0);
    }

    return $results;
  }

}
