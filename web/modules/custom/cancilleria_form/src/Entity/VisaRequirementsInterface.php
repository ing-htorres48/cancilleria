<?php

namespace Drupal\cancilleria_form\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Visa requirements entities.
 *
 * @ingroup cancilleria_form
 */
interface VisaRequirementsInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Visa requirements name.
   *
   * @return string
   *   Name of the Visa requirements.
   */
  public function getName();

  /**
   * Sets the Visa requirements name.
   *
   * @param string $name
   *   The Visa requirements name.
   *
   * @return \Drupal\cancilleria_form\Entity\VisaRequirementsInterface
   *   The called Visa requirements entity.
   */
  public function setName($name);

  /**
   * Gets the Visa requirements creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Visa requirements.
   */
  public function getCreatedTime();

  /**
   * Sets the Visa requirements creation timestamp.
   *
   * @param int $timestamp
   *   The Visa requirements creation timestamp.
   *
   * @return \Drupal\cancilleria_form\Entity\VisaRequirementsInterface
   *   The called Visa requirements entity.
   */
  public function setCreatedTime($timestamp);

}
