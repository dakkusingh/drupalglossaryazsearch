<?php

namespace Drupal\search_api_glossary\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facets\FacetInterface;
use Drupal\facets\Result\ResultInterface;
use Drupal\facets\Result\Result;
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

    foreach ($results as $result) {

      // Get the link.
      $text = $result->getDisplayValue();
      if ($show_count) {
        $text .= ' (' . $result->getCount() . ')';
      }
      if ($result->isActive()) {
        $text = '(-) ' . $text;
      }

      if (is_null($result->getUrl())) {
        $items[] = ['#markup' => $text];
      }
      else {
        $items[] = $this->buildListItems($result, $show_count);
      }
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

    $classes = ['facet-item'];

    if ($children = $result->getChildren()) {
      $items = $this->prepareLink($result, $show_count);

      $children_markup = [];
      foreach ($children as $child) {
        $children_markup[] = $this->buildChildren($child, $show_count);
      }

      $classes[] = 'expanded';
      $items['children'] = [$children_markup];

      if ($result->isActive()) {
        $items['#attributes'] = ['class' => 'active-trail'];
      }
    }
    else {
      $items = $this->prepareLink($result, $show_count);

      if ($result->isActive()) {
        $items['#attributes'] = ['class' => 'is-active'];
      }
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
    if ($result->isActive()) {
      $text = '(-) ' . $text;
    }

    if (is_null($result->getUrl())) {
      $link = ['#markup' => $text];
    }
    else {
      $link = new Link($text, $result->getUrl());
      $link = $link->toRenderable();
    }

    return $link;
  }

  /**
   * Builds a renderable array of a result.
   *
   * @param \Drupal\facets\Result\ResultInterface $child
   *   A result item.
   * @param bool $show_count
   *   A boolean that's true when the numbers should be shown.
   *
   * @return array
   *   A renderable array of the result.
   */
  protected function buildChildren(ResultInterface $child, $show_count) {
    $text = $child->getDisplayValue();
    if ($show_count) {
      $text .= ' (' . $child->getCount() . ')';
    }
    if ($child->isActive()) {
      $text = '(-) ' . $text;
    }

    if (!is_null($child->getUrl())) {
      $link = new Link($text, $child->getUrl());
      $item = $link->toRenderable();
    }
    else {
      $item = ['#markup' => $text];
    }

    $item['#wrapper_attributes'] = ['class' => ['leaf']];

    return $item;
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType($query_types) {
    return $query_types['string'];
  }

}
