<?php

namespace Drupal\search_api_glossary\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facets\FacetInterface;
use Drupal\facets\Result\ResultInterface;
use Drupal\facets\Widget\WidgetInterface;

/**
 * The GlossaryAZ widget.
 *
 * @FacetsWidget(
 *   id = "glossaryaz",
 *   label = @Translation("Glossary AZ"),
 *   description = @Translation("A simple widget that shows a Glossary AZ"),
 * )
 */
class GlossaryAZWidget implements WidgetInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    /** @var \Drupal\facets\Result\Result[] $results */
    $results = $facet->getResults();

    $items = [];

    $configuration = $facet->getWidgetConfigs();
    $show_count = empty($configuration['show_count']) ? FALSE : (bool) $configuration['show_count'];
    $enable_default_theme = empty($configuration['enable_default_theme']) ? FALSE : (bool) $configuration['enable_default_theme'];

    foreach ($results as $result) {
      $items[] = $this->buildListItems($result, $show_count);
    }

    $build = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#cache' => [
        'contexts' => [
          'url.path',
          'url.query_args',
        ],
      ],
    ];

    if ($enable_default_theme) {
      $build['#attached'] = array(
        'library' => array(
          'search_api_glossary/drupal.search_api_glossary.facet_css',
        ),
      );
    }

    return $build;
  }

  /**
   * Builds a renderable array of result items.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   * @param bool $show_count
   *   A boolean that's true when the numbers should be shown.
   *
   * @return array
   *   A renderable array of the result.
   */
  protected function buildListItems(ResultInterface $result, $show_count) {

    $classes = ['facet-item', 'glossaryaz'];
    // Not sure if glossary will have children.
    // Removed chilren processing for now.
    $items = $this->prepareLink($result, $show_count);

    if ($result->isActive()) {
      $items['#attributes'] = ['class' => 'is-active'];
      $classes[] = 'is-active';
    }
    else {
      $items['#attributes'] = ['class' => 'is-inactive'];
    }

    // Add result, no result classes.
    if ($result->getCount() == 0) {
      $classes[] = 'no-results';
    }
    else {
      $classes[] = 'yes-results';
    }

    $items['#wrapper_attributes'] = ['class' => $classes];

    return $items;
  }

  /**
   * Returns the text or link for an item.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   * @param bool $show_count
   *   A boolean that's true when the numbers should be shown.
   *
   * @return array
   *   The item, as a renderable array.
   */
  protected function prepareLink(ResultInterface $result, $show_count) {
    $text = $result->getDisplayValue();

    if ($show_count) {
      $text .= ' (' . $result->getCount() . ')';
    }

    if (is_null($result->getUrl()) || $result->getCount() == 0) {
      $link = ['#markup' => $text];
    }
    else {
      $link = new Link($text, $result->getUrl());
      $link = $link->toRenderable();
    }

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {

    $form['show_count'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show count per Glossary item'),
    ];

    if (!is_null($config)) {
      $widget_configs = $config->get('widget_configs');
      if (isset($widget_configs['show_count'])) {
        $form['show_count']['#default_value'] = $widget_configs['show_count'];
      }
    }

    $form['enable_default_theme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use default Glossary AZ Theme'),
    ];

    if (!is_null($config)) {
      $widget_configs = $config->get('widget_configs');
      if (isset($widget_configs['enable_default_theme'])) {
        $form['enable_default_theme']['#default_value'] = $widget_configs['enable_default_theme'];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType($query_types) {
    return $query_types['string'];
  }

}
