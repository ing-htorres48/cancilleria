<?php

namespace Drupal\cancilleria_form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Costs means entities.
 *
 * @ingroup cancilleria_form
 */
class CostsMeansListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Costs means ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\cancilleria_form\Entity\CostsMeans $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.costs_means.edit_form',
      ['costs_means' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
