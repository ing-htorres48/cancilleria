<?php

namespace Drupal\cancilleria_form;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Visa requirements entity.
 *
 * @see \Drupal\cancilleria_form\Entity\VisaRequirements.
 */
class VisaRequirementsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\cancilleria_form\Entity\VisaRequirementsInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished visa requirements entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published visa requirements entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit visa requirements entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete visa requirements entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add visa requirements entities');
  }


}
