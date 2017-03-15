<?php

namespace Drupal\search_api_glossary\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\facets\Result\Result;
use Drupal\Core\Url;

/**
 * Provides a processor to rewrite facet results to pad out missing alpha.
 *
 * @FacetsProcessor(
 *   id = "glossaryaz_all_items_processor",
 *   label = @Translation("All items in Glossary AZ"),
 *   description = @Translation("Option to show All items in Glossary AZ. Make sure URL handler runs before this processor (see: Advanced settings > Build Stage)"),
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
    $show_all_item = new Result('All', 'All', count($results));

    // Process the results count.
    $show_all_item_count = 0;
    foreach ($results as $result) {
      $show_all_item_count += $result->getCount();
    }
    // Set the total results.
    $show_all_item->setCount($show_all_item_count);

    // Deal with the ALL Items path.
    $link = $facet->getFacetSource()->getPath();

    // Set the path.
    $link->setAbsolute();
    $show_all_item->setUrl($link);

    // If no other facets are selected, default to ALL.
    if (empty($facet->getActiveItems())) {
      $show_all_item->setActiveState(TRUE);
    }

    // All done.
    $results[] = $show_all_item;

    return $results;
  }

}
