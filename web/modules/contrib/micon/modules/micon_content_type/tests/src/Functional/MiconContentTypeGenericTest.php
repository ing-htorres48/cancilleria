<?php

namespace Drupal\Tests\micon_content_type\Functional;

use Drupal\Tests\system\Functional\Module\GenericModuleTestBase;

/**
 * Generic module test for micon_content_type.
 *
 * @group micon_content_type
 */
class MiconContentTypeGenericTest extends GenericModuleTestBase {

  /**
   * {@inheritDoc}
   */
  protected function assertHookHelp(string $module): void {
    // Don't do anything here. Just overwrite this useless method, so we do
    // don't have to implement hook_help().
  }

}
