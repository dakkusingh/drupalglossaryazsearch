<?php

// TODO: port to d8
/**
 * Alter Solr documents before they are sent to Solr for indexing.
 *
 * @param array $documents
 *   An array of SearchApiSolrDocument objects ready to be indexed, generated
 *   from $items array.
 * @param SearchApiIndex $index
 *   The search index for which items are being indexed.
 * @param array $items
 *   An array of items being indexed.
 */
function search_api_glossary_search_api_solr_documents_alter(array &$documents, SearchApiIndex $index, array $items) {
  $searcher = 'search_api@' . $index->machine_name;

  $fields = _search_api_glossary_get_field();
  $field_names = $index->server()->getFieldNames($index);

  foreach ($fields as $field) {
    if ($facet = facetapi_facet_load($field, $searcher)) {
      $adapter = facetapi_adapter_load($searcher);
      $facet_settings = $adapter->getFacetSettingsGlobal($facet);
      $field_key = $facet['field'] . '_field';

      if (!empty($facet_settings->settings[$field_key])) {
        $source_field_key = $facet_settings->settings[$field_key];

        foreach ($documents as $document) {
          $source_field = $document->getField($source_field_key);

          // is source field a "string" or "text"?
          if (0 === strpos($source_field['name'], 'tm_')) {
            // $source_field['value'] is an array
            $source_field_value = $source_field['value'][0];
          }
          elseif (0 === strpos($source_field['name'], 'ss_')) {
            // $source_field['value'] is an string
            $source_field_value = $source_field['value'];
          }

          if (is_string($source_field_value) && (drupal_strlen($source_field_value) > 0)) {
            // get the first letter of $source_field_value, eg CAT = C
            $first_letter = strtoupper($source_field_value[0]);

            // Group the $first_letter into its container bin.
            $value = search_api_glossary_getter_helper($first_letter);

            // Set the values in Solr
            $document->setField($field_names[$field], $value);
          }

        }
      }

    }
  }
}
