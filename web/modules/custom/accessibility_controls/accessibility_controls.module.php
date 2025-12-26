<?php

/**
 * @file
 * Accessibility Controls module.
 */

use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Implements hook_page_attachments().
 */
function accessibility_controls_page_attachments(array &$attachments) {
  $attachments['#attached']['library'][] = 'accessibility_controls/controls';
}
