<?php

namespace Drupal\cancilleria_form\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Costs means entities.
 *
 * @ingroup cancilleria_form
 */
interface CostsMeansInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Costs means name.
   *
   * @return string
   *   Name of the Costs means.
   */
  public function getName();

  /**
   * Sets the Costs means name.
   *
   * @param string $name
   *   The Costs means name.
   *
   * @return \Drupal\cancilleria_form\Entity\CostsMeansInterface
   *   The called Costs means entity.
   */
  public function setName($name);

  /**
   * Gets the Costs means creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Costs means.
   */
  public function getCreatedTime();

  /**
   * Sets the Costs means creation timestamp.
   *
   * @param int $timestamp
   *   The Costs means creation timestamp.
   *
   * @return \Drupal\cancilleria_form\Entity\CostsMeansInterface
   *   The called Costs means entity.
   */
  public function setCreatedTime($timestamp);

}
