<?php

namespace Drupal\cancilleria_form;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Costs means entity.
 *
 * @see \Drupal\cancilleria_form\Entity\CostsMeans.
 */
class CostsMeansAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\cancilleria_form\Entity\CostsMeansInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished costs means entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published costs means entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit costs means entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete costs means entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add costs means entities');
  }


}
