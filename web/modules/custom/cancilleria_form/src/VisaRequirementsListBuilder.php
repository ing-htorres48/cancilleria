<?php

namespace Drupal\cancilleria_form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Visa requirements entities.
 *
 * @ingroup cancilleria_form
 */
class VisaRequirementsListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Visa requirements ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\cancilleria_form\Entity\VisaRequirements $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.visa_requirements.edit_form',
      ['visa_requirements' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
