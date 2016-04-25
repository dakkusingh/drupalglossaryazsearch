<?php

namespace Drupal\search_api_glossary\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

use Drupal\Core\Entity\Controller\EntityListController;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Plugin implementation of the 'glossaryaz' field type.
 *
 * @FieldType(
 *   id = "glossary_az",
 *   label = @Translation("Glossary AZ"),
 *   module = "search_api_glossary",
 *   default_widget = "glossary_az",
 *   description = @Translation("An entity field containing a Glossary AZ.") * )
 */
class GlossaryAZItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'glossary_az_source' => NULL,
      'glossary_az_grouping' => array('glossary_az_grouping_09', 'glossary_az_grouping_other'),
      //'is_ascii' => FALSE,
      'case_sensitive' => FALSE,
    ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Glossary AZ Value'))
      ->setRequired(FALSE);

    // TODO: Lock this field to prevent editing

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  /*public function getConstraints() {
    $constraints = parent::getConstraints();

    if ($max_length = $this->getSetting('max_length')) {
      $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
      $constraints[] = $constraint_manager->create('ComplexData', array(
        'value' => array(
          'Length' => array(
            'max' => $max_length,
            'maxMessage' => t('%name: may not be longer than @max characters.', array(
              '%name' => $this->getFieldDefinition()->getLabel(),
              '@max' => $max_length
            )),
          ),
        ),
      ));
    }

    return $constraints;
  }*/

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['value'] = strtoupper($random->word(mt_rand(1, 1)));
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }


  /**
   * {@inheritdoc}
   */
  public function preSave() {

  }


  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    // TODO Maybe there is a better way to do all of this
    $field_types = $this->getFieldDefinition();
    $bundle = $this->getFieldDefinition()->get('bundle');
    $entity_type = $this->getFieldDefinition()->get('entity_type');
    $fields = \Drupal::entityManager()->getFieldDefinitions($entity_type, $bundle);

    // TODO Maybe there is a better way to identify
    $included_types = array(
      'string',
      'text_with_summary',
      'integer',
    );

    foreach ($fields as $field_definition) {

      /*ksm(array(
        //$field_definition,
        $field_definition->getFieldStorageDefinition(),
        //$field_definition->getDisplayOptions('form'),
        //$field_definition->getItemDefinition()->getPropertyDefinitions(),
        //$field_definition->getItemDefinition()->getPropertyDefinitions(),
        //$field_definition->getItemDefinition()->getPropertyDefinitions()->getDataType(),
        $field_definition->getType(),
        $field_definition->getName())
      );*/

      // TODO this could do with improvement
      if (in_array($field_definition->getType(), $included_types)) {
        $options[$field_definition->getName()] = $field_definition->getLabel();
      }

    }

    $element['glossary_az_source'] = array(
      '#type' => 'select',
      '#title' => t('Source field for Glossary AZ Index'),
      '#options' => $options,
      '#default_value' => $this->getSetting('glossary_az_source'),
      '#required' => TRUE,
      #'#disabled' => $has_data,
      '#size' => 1,
    );

    $element['glossary_az_grouping'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Group Glossary AZ Indexes'),
      '#description' => t('When grouping is enabled, individual values such as 1, 2, 3 will go into a grouped index like "0-9"'),
      '#options' => array(
        'glossary_az_grouping_az' => 'Group Alphabetic Values (A-Z)',
        'glossary_az_grouping_09' => 'Group Numeric Values (0-9)',
        'glossary_az_grouping_other' => 'Group Non Alpha Numeric Values (#)',
      ),
      '#default_value' => $this->getSetting('glossary_az_grouping'),
      '#required' => FALSE,
      #'#disabled' => $has_data,
      '#size' => 1,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    // TODO remove and cleanup the form

    //$form['required']['#type'] = 'hidden';
    //$form['description']['#type'] = 'hidden';

    //ksm($form);
    //return $form;
    return array();
  }
}
