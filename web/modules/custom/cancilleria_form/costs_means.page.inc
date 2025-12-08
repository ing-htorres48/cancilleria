<?php

/**
 * @file
 * Contains costs_means.page.inc.
 *
 * Page callback for Costs means entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Costs means templates.
 *
 * Default template: costs_means.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_costs_means(array &$variables) {
  // Fetch CostsMeans Entity Object.
  $costs_means = $variables['elements']['#costs_means'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
