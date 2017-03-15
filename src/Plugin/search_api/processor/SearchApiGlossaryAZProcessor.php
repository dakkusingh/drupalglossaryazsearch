<?php

namespace Drupal\search_api_glossary\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the item's URL to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "SearchApiGlossaryAZProcessor",
 *   label = @Translation("Search API glossary processor"),
 *   description = @Translation("Exposes glossary computed fields to Search API."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class SearchApiGlossaryAZProcessor extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = array();

    if ($datasource && $datasource->getEntityTypeId() == 'node') {
      // Load up config and loop though settings.
      if ($config = \Drupal::config('search_api_glossary.settings')) {
        $search_api_glossary_settings = $config->get();
        if (!empty($search_api_glossary_settings)) {
          // Loop through the saved config from.
          // Search API field settings form.
          foreach ($search_api_glossary_settings as $value) {
            // Create the fields.
            if ($datasource->getEntityTypeId() == $value['entity_type'] && $value['enabled'] == 1) {
              $definition = array(
                'label' => $value['glossary_field_name'],
                'description' => $value['glossary_field_desc'],
                'type' => 'string',
                'processor_id' => $this->getPluginId(),
              );
              $properties[$value['glossary_field_id']] = new ProcessorProperty($definition);
            }
          }
        }
      }
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    // Load up config and loop though settings.
    if ($config = \Drupal::config('search_api_glossary.settings')) {
      $search_api_glossary_settings = $config->get();

      $item_fields = $item->getFields();

      // Loop through all fields.
      foreach ($item_fields as $field_name => $field_values) {
        if (array_key_exists($field_name, $search_api_glossary_settings) && $search_api_glossary_settings[$field_name]['enabled'] == 1 && !empty($field_values->getValues())) {
          $source_field_value = $field_values->getValues()[0];

          // Glossary process.
          $glossary_value = search_api_glossary_glossary_getter($source_field_value, $search_api_glossary_settings[$field_name]['glossary_az_grouping']);
          $target_field_id = $search_api_glossary_settings[$field_name]['glossary_field_id'];

          // Set the Target Glossary value.
          if (empty($item->getField($target_field_id)->getValues())) {
            $item->getField($target_field_id)->addValue($glossary_value);
          }
        }
      }
    }
  }

}
