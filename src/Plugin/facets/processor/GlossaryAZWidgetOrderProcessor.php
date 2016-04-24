<?php

namespace Drupal\search_api_glossary\Plugin\facets\processor;

use Drupal\facets\Processor\WidgetOrderPluginBase;
use Drupal\facets\Processor\WidgetOrderProcessorInterface;
use Drupal\facets\Result\Result;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;

/**
 * A processor that orders the results by display value.
 *
 * @FacetsProcessor(
 *   id = "glossaryaz_widget_order",
 *   label = @Translation("Sort by Glossary AZ"),
 *   description = @Translation("Sort order for Glossary AZ items."),
 *   stages = {
 *     "build" = 100
 *   }
 * )
 */
class GlossaryAZWidgetOrderProcessor extends WidgetOrderPluginBase implements WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function sortResults(array $results, $order = '') {

    // Get the custom sort order from config.
    $sort_options_by_weight = $this->sortConfigurationWeight($order);

    // Initialise an empty array and populate
    // it with options in the same order as the sort
    // order defined in the config
    $glossary_results = array();
    foreach ($sort_options_by_weight as $sort_option_by_weight_id => $sort_option_by_weight_weight) {
      //$newarray = array_values($glossary_results[$sort_option_by_weight_id]);
      //$newarray = array_merge($newarray, array_values($glossary_results[$sort_option_by_weight_id]));
      $glossary_results[$sort_option_by_weight_id] = array();
      $glossary_results[$sort_option_by_weight_id] = array();
      $glossary_results[$sort_option_by_weight_id] = array();
      $glossary_results[$sort_option_by_weight_id] = array();
    }

    // Since our new array is already in
    // the sort order defined in the config
    // lets step through the results and populate
    // results into respective containers.
    foreach ($results as $result) {
      if ($result->getRawValue() == 'All') {
        $glossary_results['glossaryaz_sort_all'][$result->getRawValue()] = $result;
      }
      // Is it a number? or maybe grouped number eg 0-9 (technically a string).
      elseif ($result->getRawValue() == '0-9' || ctype_digit($result->getRawValue())) {
        $glossary_results['glossaryaz_sort_09'][$result->getRawValue()] = $result;
      }
      // Is it alpha?
      elseif (ctype_alpha($result->getRawValue())) {
        $glossary_results['glossaryaz_sort_az'][$result->getRawValue()] = $result;
      }
      // Non alpha numeric.
      else {
        $glossary_results['glossaryaz_sort_other'][$result->getRawValue()] = $result;
      }
    }

    ksort($glossary_results['glossaryaz_sort_az']);
    ksort($glossary_results['glossaryaz_sort_09']);
    ksort($glossary_results['glossaryaz_sort_other']);

    // Flatten the array to same structure as $results.
    $glossary_results_sorted = array();
    foreach ($glossary_results as $glossary_result) {
      $glossary_results_sorted = array_merge($glossary_results_sorted, array_values($glossary_result));
    }

    // And its done.
    return $glossary_results_sorted;
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processors = $facet->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ? $processors[$this->getPluginId()] : NULL;

    // Get the weight options.
    $sort_options = !is_null($config) ? $config->getConfiguration()['sort'] : $this->defaultConfiguration();
    $sort_options_by_weight = $this->sortConfigurationWeight($sort_options);

    // Build the form.
    $build['sort'] = array(
      '#tree' => TRUE,
      '#type' => 'table',
      '#attributes' => array(
        'id' => 'glossaryaz-sort-widget',
      ),
      '#header' => array(
        $this->t('Sort By'),
        $this->t('Weight'),
      ),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'glossaryaz-sort-weight',
        ),
      ),
    );

    foreach ($sort_options_by_weight as $sort_option_key => $sort_option_weight) {
      $build['sort'][$sort_option_key]['#attributes']['class'][] = 'draggable';
      $build['sort'][$sort_option_key]['#attributes']['class'][] = 'glossaryaz-sort-weight--' . $sort_option_key;
      $build['sort'][$sort_option_key]['#weight'] = $sort_option_weight;
      $build['sort'][$sort_option_key]['sort_by']['#plain_text'] = $this->defaultConfiguration()[$sort_option_key]['name'];

      $build['sort'][$sort_option_key]['weight'] = array(
          '#type' => 'weight',
          '#delta' => count($this->defaultConfiguration()),
          '#default_value' => $sort_option_weight,
          '#attributes' => array(
            'class' => array(
              'glossaryaz-sort-weight',
            ),
          ),
      );
    }

    return $build;
  }


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $sort_options_deafult = array(
      'glossaryaz_sort_az' => array(
        'weight' => 1,
        'name' => $this->t('Alpha (A-Z)'),
      ),
      'glossaryaz_sort_09' => array(
        'weight' => 2,
        'name' => $this->t('Numeric (0-9)'),
      ),
      'glossaryaz_sort_other' => array(
        'weight' => 3,
        'name' => $this->t('Other (#)'),
      ),
      'glossaryaz_sort_all' => array(
        'weight' => -1,
        'name' => $this->t('All'),
      ),
    );

    return $sort_options_deafult;
  }


  /**
   * {@inheritdoc}
   */
  public function sortConfigurationWeight($sort_options) {
    foreach ($sort_options as $sort_option_id => $sort_option) {
      $sort_options_by_weight[$sort_option_id] = $sort_option['weight'];
    }

    // Sort by weight options.
    asort($sort_options_by_weight);
    return $sort_options_by_weight;
  }

  /**
   * Sorts default.
   */
/*  protected static function sortGlossaryAZDefault(Result $a, Result $b) {

    // TODO Maybe check if the values are present.
    $a_value = $a->getRawValue();
    $b_value = $b->getRawValue();

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

  }*/

}
