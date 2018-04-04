<?php

namespace Drupal\search_api_glossary\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\facets\FacetInterface;
use Drupal\facets\Result\ResultInterface;
use Drupal\facets\Widget\WidgetPluginInterface;
use Drupal\facets\Widget\WidgetPluginBase;
use Drupal\search_api_glossary\GlossaryHelper;

/**
 * The GlossaryAZ widget.
 *
 * @FacetsWidget(
 *   id = "glossaryaz",
 *   label = @Translation("Glossary AZ"),
 *   description = @Translation("A simple widget that shows a Glossary AZ"),
 * )
 */
class GlossaryAZWidget extends WidgetPluginBase implements WidgetPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    // Are we dealing with Glossary field?
    // TODO Figure out a better way to set Widget conditions.
    // See https://www.drupal.org/node/2877691.
    $is_glossary_field = GlossaryHelper::glossaryFacetFieldCheker($facet);
    if (!$is_glossary_field) {
      return [];
    }

    /** @var \Drupal\facets\Result\Result[] $results */
    $results = $facet->getResults();
    $items = [];

    $configuration = $facet->getWidget()['config'];
    $enable_default_theme = empty($configuration['enable_default_theme']) ? FALSE : (bool) $configuration['enable_default_theme'];

    foreach ($results as $result) {
      $items[] = $this->buildListItems($facet, $result);
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
      $build['#attached'] = [
        'library' => [
          'search_api_glossary/drupal.search_api_glossary.facet_css',
        ],
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildListItems(FacetInterface $facet, ResultInterface $result) {
    $classes = ['facet-item', 'glossaryaz'];
    // Not sure if glossary will have children.
    // Removed chilren processing for now.
    $items = $this->prepareLink($result);

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
   *
   * @return array
   *   The item, as a renderable array.
   */
  protected function prepareLink(ResultInterface $result) {
    $configuration = $this->getConfiguration();
    $show_count = empty($configuration['show_count']) ? FALSE : (bool) $configuration['show_count'];

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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $form['show_count'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show count per Glossary item'),
    ];
    $form['enable_default_theme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use default Glossary AZ Theme'),
    ];

    $config = $facet->getWidget()['config'];
    if (!is_null($config)) {
      if (isset($config['show_count'])) {
        $form['show_count']['#default_value'] = $config['show_count'];
      }
      if (isset($config['enable_default_theme'])) {
        $form['enable_default_theme']['#default_value'] = $config['enable_default_theme'];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType() {
    return 'string';
  }

}
