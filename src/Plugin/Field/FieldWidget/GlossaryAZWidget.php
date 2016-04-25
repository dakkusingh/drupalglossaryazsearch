<?php

namespace Drupal\search_api_glossary\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Component\Utility\NestedArray;

/**
 * Plugin implementation of the 'glossary_az' widget.
 *
 * @FieldWidget(
 *   id = "glossary_az",
 *   label = @Translation("Glossary AZ Widget"),
 *   field_types = {
 *     "glossary_az"
 *   }
 * )
 */
class GlossaryAZWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'size' => 60,
      'placeholder' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = array(
      '#type' => 'number',
      '#title' => t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    );
    $elements['placeholder'] = array(
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: !size', array('!size' => $this->getSetting('size')));
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', array('@placeholder' => $this->getSetting('placeholder')));
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];

    $element['value'] = $element + array(
      '#title' => $this->t('Glossary AZ for ' . $this->getFieldSetting('glossary_az_source')),
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#element_validate' => array(
        array($this, 'validate'),
      ),
    );

    return $element;
  }

  /**
   * Validate the Glossary Widget
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];

    $source_field = $this->getFieldSetting('glossary_az_source');
    $glossary_az_grouping = $this->getFieldSetting('glossary_az_grouping');

    // TODO there seems to be some weird notice about invalid value
    //$source_value2 = $form_state->getValue('title');
    //ksm($source_value2);

    // TODO Surely there has to be a better way
    $source_value = NestedArray::getValue($form_state->getValues(), array($source_field, '0'))['value'];
    //ksm($source_value);

    // Get the Glossary AZ Index value.
    $glossary_az = $this->glossaryGetter($source_value, $glossary_az_grouping);

    // Check in place to avoid duplicated effort.
    if ($glossary_az != $value) {
      $form_state->setValueForElement($element, $glossary_az);
    }
  }


  /**
   * Getter callback for title_az_glossary property.
   */
  private function glossaryGetter($source_value, $glossary_az_grouping) {
    $first_letter = strtoupper($source_value)[0];
    return $this->glossaryGetterHelper($first_letter, $glossary_az_grouping);
  }

  /**
   * Getter Helper for Alpha Numeric Keys.
   */
  private function glossaryGetterHelper($first_letter, $glossary_az_grouping) {
    $key = $first_letter;
    //ksm($glossary_az_grouping);

    // Is it Alpha?
    // Do we have Alpha grouping?
    if (ctype_alpha($first_letter) && in_array('glossary_az_grouping_az', $glossary_az_grouping)) {
      $first_letter = "A-Z";
    }
    // Is it a number?
    // Do we have Numeric grouping?
    elseif (ctype_digit($first_letter) && in_array('glossary_az_grouping_09', $glossary_az_grouping)) {
      $first_letter = "0-9";
    }
    // Catch non alpha numeric.
    // Do we have Non Alpha Numeric grouping?
    elseif (in_array('glossary_az_grouping_other', $glossary_az_grouping)) {
      $first_letter = "#";
    }

    

    // All done, return the key
    return $first_letter;
  }

}
