<?php

namespace Drupal\search_api_glossary;

/**
 * Search Api Glossary AZ Helper class.
 *
 * @package Drupal\search_api_glossary
 */
class GlossaryHelper {

  /**
   * Getter callback for title_az_glossary property.
   */
  public function glossaryGetter($source_value, $glossary_az_grouping) {
    // Trim it, then get first letter, then uppercase it.
    $first_letter = mb_strtoupper(mb_substr(trim($source_value), 0, 1));

    // Allow other modules to hook in and alter the first letter.
    // TODO Replace with Event Subscriber.
    // \Drupal::moduleHandler()->alter('search_api_glossary_source', $first_letter);

    // Finally check groupings and alter the first letter.
    return $this->glossaryGetterHelper($first_letter, array_values($glossary_az_grouping));
  }

  /**
   * Getter Helper for Alpha Numeric Keys.
   */
  public function glossaryGetterHelper($first_letter, $glossary_az_grouping) {
    // Do we have Alpha grouping?
    if (in_array('grouping_az', $glossary_az_grouping, TRUE)) {
      // Is it Alpha?
      // See http://php.net/manual/en/regexp.reference.unicode.php
      if (preg_match('/^\p{L}+$/u', $first_letter)) {
        // TODO Figure out how to get AZ equivalent in native language.
        $first_letter = "A-Z";
      }
    }

    // Do we have Numeric grouping?
    elseif (in_array('grouping_09', $glossary_az_grouping, TRUE)) {
      // Is it a number?
      // See http://php.net/manual/en/regexp.reference.unicode.php
      if (preg_match('/^\p{N}+$/u', $first_letter)) {
        // TODO Figure out how to get 09 equivalent in native language.
        $first_letter = "0-9";
      }
    }

    // Catch non alpha numeric.
    // Do we have Non Alpha Numeric grouping?
    elseif (in_array('grouping_other', $glossary_az_grouping, TRUE)) {
      // TODO Figure out how to get # equivalent in native language.
      $first_letter = "#";
    }

    // TODO Maybe allow a final alter as the easy way to change groups?
    // TODO Replace with Event Subscriber.
    return $first_letter;
  }

  /**
   * Facet Helper for to check glossary field.
   */
  public static function glossaryFacetFieldCheker($facet) {
    // Load up the search index and processor.
    $glossary_processor = $facet->getFacetSource()->getIndex()->getProcessor('glossary');

    // Resolve fields.
    $glossary_field_id = $facet->getFieldIdentifier();

    // Check if chosen field is glossary or not.
    return $glossary_processor->checkFieldName($glossary_field_id);
  }

}
