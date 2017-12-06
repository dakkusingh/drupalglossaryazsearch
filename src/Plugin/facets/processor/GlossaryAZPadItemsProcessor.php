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

    if (!array_key_exists('glossary', $facet->getFacetSource()->getIndex()->getProcessors())) {
      // Glossary processor is not enabled, nothing to do.
      return $results;
    }

    // All good?
    // Load up the search index and processor.
    $glossary_processor = $facet->getFacetSource()->getIndex()->getProcessor('glossary');
    $glossary_processor_config_fields = $glossary_processor->getConfig();

    // Resolve fields.
    $glossary_field_id = $facet->getFieldIdentifier();
    $parent_field_id = $glossary_processor->getFieldName($glossary_field_id);

    // Finally get group values.
    $glossary_az_grouping = array_values($glossary_processor_config_fields[$parent_field_id]['grouping']);

    $glossary_array = [];
    // If Alpha grouping is not set, pad alpha.
    if (!in_array('grouping_az', $glossary_az_grouping, TRUE)) {
      $glossary_array = array_merge($glossary_array, range('A', 'Z'));
    }
    else {
      $glossary_array[] = "A-Z";
    }

    // If Numeric grouping is not set, pad alpha.
    if (!in_array('grouping_09', $glossary_az_grouping, TRUE)) {
      $glossary_array = array_merge($glossary_array, array_map('strval', range('0', '9')));
    }
    else {
      $glossary_array[] = "0-9";
    }

    // Do we have Non Alpha Numeric grouping?
    if (in_array('grouping_other', $glossary_az_grouping, TRUE)) {
      $glossary_array[] = "#";
    }

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
      $results[] = new Result($facet, $glossary, $glossary, 0);
    }

    return $results;
  }

}
