<?php

/**
 * Helper function to return list of fields.
 */
function _search_api_glossary_get_field() {
  $fields = array(
    'title_az_glossary',
  );

  return $fields;
}


// TODO: port to d8
/**
 * Getter callback for title_az_glossary property.
 */
function search_api_glossary_title_getter_callback($node, $name) {
  // @TODO: remove hardcoded node title field and expose this as a setting.
  $first_letter = strtoupper($node->title[0]);
  $key = search_api_glossary_getter_helper($first_letter);
  return $key;
}


// TODO: port to d8
/**
 * Getter Helper for Alpha Numeric Keys.
 */
function search_api_glossary_getter_helper($first_letter) {
  // Is it alpha?
  if (ctype_alpha($first_letter)) {
    $key = $first_letter;
  }
  // Is it a number?
  elseif (ctype_digit($first_letter)) {
    $key = "0-9";
  }
  // Catch all.
  else {
    $key = "#";
  }

  return $key;
}
