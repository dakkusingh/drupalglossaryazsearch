<?php

namespace Drupal\search_api_glossary\Plugin\search_api\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\FieldsProcessorPluginBase;
use Drupal\search_api\Utility\DataTypeHelperInterface;
use Drupal\search_api\Utility\Utility;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\Component\Utility\Html;
use Drupal\search_api_glossary\SearchApiGlossaryAZHelper;

/**
 * Adds the item's AZ to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "SearchApiGlossaryAZProcessor",
 *   label = @Translation("Search API glossary processor"),
 *   description = @Translation("Exposes glossary computed fields to Search API."),
 *   stages = {
 *     "add_properties" = 0,
 *     "pre_index_save" = 0,
 *   },
 *   locked = false,
 *   hidden = false,
 * )
 */
class SearchApiGlossaryAZProcessor extends FieldsProcessorPluginBase {

  /**
   * The data type helper.
   *
   * @var \Drupal\search_api\Utility\DataTypeHelperInterface|null
   */
  protected $dataTypeHelper;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      // TODO This logic would now be based on new config
      /*$config = \Drupal::config('search_api_glossary.settings');
      $search_api_glossary_settings = $config->get();

      if (!empty($search_api_glossary_settings)) {
        // Loop through the saved config from.
        // Search API field settings form.
        foreach ($search_api_glossary_settings as $value) {
          // Create the fields.
          if ($value['enabled'] == 1) {
            $definition = [
              'label' => $this->t($value['glossary_field_name']),
              'description' => $this->t($value['glossary_field_desc']),
              'type' => 'string',
              'processor_id' => $this->getPluginId(),
            ];
            $properties[$value['glossary_field_id']] = new ProcessorProperty($definition);
          }
        }
      }*/
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  /*public function addFieldValues(ItemInterface $item) {
    // Load up config and loop though settings.
    if ($config = \Drupal::config('search_api_glossary.settings')) {
      $search_api_glossary_settings = $config->get();

      $item_fields = $item->getFields();

      // Loop through all fields.
      foreach ($item_fields as $field_name => $field_values) {
        if (array_key_exists($field_name, $search_api_glossary_settings) && $search_api_glossary_settings[$field_name]['enabled'] == 1 && !empty($field_values->getValues())) {
          $source_field_value = $field_values->getValues()[0];

          // Glossary process.
          $glossary_value = SearchApiGlossaryAZHelper::glossaryGetter($source_field_value, $search_api_glossary_settings[$field_name]['glossary_az_grouping']);
          $target_field_id = $search_api_glossary_settings[$field_name]['glossary_field_id'];

          // Set the Target Glossary value.
          if (empty($item->getField($target_field_id)->getValues())) {
            $item->getField($target_field_id)->addValue($glossary_value);
          }
        }
      }
    }
  }*/

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'field_enabled' => 0,
      'grouping_defaults' => [
        'grouping_other' => 'grouping_other',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $fields = $this->index->getFields();

    $form['glossarytable'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('Glossary Grouping')
      ],
    ];

    foreach ($fields as $name => $field) {
      // Filter out hidden fields
      // and fields that do not match
      // our required criteria.
      if ($field->isHidden() == FALSE && $this->testType($field->getType())) {

        // Check the config if the field has been enabled?
        $field_enabled = $this->configuration['field_enabled'];
        if (isset($this->configuration['glossarytable'][$name]['glossary']) &&
            $this->configuration['glossarytable'][$name]['glossary'] == 1) {
          $field_enabled = $this->configuration['glossarytable'][$name]['glossary'];
        }

        // Check the config if the field has been enabled?
        $field_gouping = $this->configuration['grouping_defaults'];
        if (isset($this->configuration['glossarytable'][$name]['grouping'])) {
          $field_gouping = $this->configuration['glossarytable'][$name]['grouping'];
        }

        $form['glossarytable'][$name]['glossary'] = [
          '#type' => 'checkbox',
          '#title' => Html::escape($field->getPrefixedLabel()),
          '#default_value' => $field_enabled,
        ];

        // Finally add the glossary grouping options per field.
        $form['glossarytable'][$name]['grouping'] = [
          '#type' => 'checkboxes',
          '#description' => t('When grouping is enabled, individual values such as 1, 2, 3 will get grouped like "0-9"'),
          '#options' => [
            'grouping_az' => 'Group Alphabetic (A-Z)',
            'grouping_09' => 'Group Numeric (0-9)',
            'grouping_other' => 'Group Non Alpha Numeric (#)',
          ],
          // TODO There is a suspected bug where these dot always get saved
          '#default_value' => $field_gouping,
          '#required' => FALSE,
          '#states' => [
            'visible' => [
                [':input[name="processors[SearchApiGlossaryAZProcessor][settings][glossarytable][' . $name . '][glossary]"]' => ['checked' => TRUE]],
            ],
          ],
        ];

      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues('glossarytable'));
  }

  /**
   * {@inheritdoc}
   */
  public function preIndexSave() {
    // Automatically add field to index if processor is enabled.
    //$field = $this->ensureField(NULL, $this->target_field_id, 'integer');
    // Hide the field.
    //$field->setHidden();
  }

  /**
   * {@inheritdoc}
   */
  protected function testType($type) {
    return $this->getDataTypeHelper()
      ->isTextType($type, ['text', 'string', 'integer']);
  }

}
