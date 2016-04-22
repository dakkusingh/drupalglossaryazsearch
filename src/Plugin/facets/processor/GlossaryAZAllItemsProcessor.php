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
 *   id = "glossaryaz_all_items_processor",
 *   label = @Translation("All items in Glossary AZ"),
 *   description = @Translation("Option to show All items in Glossary AZ"),
 *   stages = {
 *     "build" = 10
 *   }
 * )
 */
class GlossaryAZAllItemsProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    // TODO needs more love, just a POC
    // TODO add url handling etc
    $show_all_item = new Result('All', 'All', count($results));

    // TODO get actual counts
    $results[] = $show_all_item;

    return $results;
  }

}
