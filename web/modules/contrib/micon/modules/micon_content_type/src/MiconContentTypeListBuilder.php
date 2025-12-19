<?php

namespace Drupal\micon_content_type;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeTypeListBuilder;

/**
 * Provides a listing of ContentType.
 */
class MiconContentTypeListBuilder extends NodeTypeListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['icon'] = $this->t('Icon');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $icon = micon_content_type_icon($entity);
    $row['icon']['data']['#markup'] = $icon ? micon()->setIcon($icon) : '';
    return $row + parent::buildRow($entity);
  }

}
