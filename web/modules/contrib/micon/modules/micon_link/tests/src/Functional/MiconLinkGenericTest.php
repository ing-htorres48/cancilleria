<?php

namespace Drupal\Tests\micon_link\Functional;

use Drupal\Tests\system\Functional\Module\GenericModuleTestBase;

/**
 * Generic module test for micon_link.
 *
 * @group micon_link
 */
class MiconLinkGenericTest extends GenericModuleTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'micon_basic_configs',
    'micon_link_config_test',
    'link',
  ];

  /**
   * {@inheritDoc}
   */
  protected function assertHookHelp(string $module): void {
    // Don't do anything here. Just overwrite this useless method, so we do
    // don't have to implement hook_help().
  }

}
