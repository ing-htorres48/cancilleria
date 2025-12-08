<?php

/**
 * @file
 * Contains visa_requirements.page.inc.
 *
 * Page callback for Visa requirements entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Visa requirements templates.
 *
 * Default template: visa_requirements.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_visa_requirements(array &$variables) {
  // Fetch VisaRequirements Entity Object.
  $visa_requirements = $variables['elements']['#visa_requirements'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
