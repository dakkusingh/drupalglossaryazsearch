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
  public function sortResults(array $results, $order = 'ASC') {
    // TODO figure out a custom sort instead of ASC DESC
    usort($results, 'self::sortGlossaryAZDefault');
    return $results;
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processors = $facet->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ? $processors[$this->getPluginId()] : NULL;

    // Get the weight options.
    $sort_options = !is_null($config) ? $config->getConfiguration()['glossaryaz_sort'] : $this->defaultConfiguration();
    foreach ($sort_options as $sort_option_id => $sort_option) {
      $sort_options_by_weight[$sort_option_id] = $sort_option['weight'];
    }

    // Sort by weight options.
    asort($sort_options_by_weight);

    // Build the form.
    $build['glossaryaz_sort'] = array(
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
      $build['glossaryaz_sort'][$sort_option_key]['#attributes']['class'][] = 'draggable';
      $build['glossaryaz_sort'][$sort_option_key]['#attributes']['class'][] = 'glossaryaz-sort-weight--' . $sort_option_key;
      $build['glossaryaz_sort'][$sort_option_key]['#weight'] = $sort_option_weight;
      $build['glossaryaz_sort'][$sort_option_key]['sort_by']['#plain_text'] = $this->defaultConfiguration()[$sort_option_key]['name'];

      $build['glossaryaz_sort'][$sort_option_key]['weight'] = array(
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
