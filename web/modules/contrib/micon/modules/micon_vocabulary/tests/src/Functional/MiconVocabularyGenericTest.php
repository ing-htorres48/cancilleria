<?php

namespace Drupal\Tests\micon_vocabulary\Functional;

use Drupal\Tests\system\Functional\Module\GenericModuleTestBase;

/**
 * Generic module test for micon_vocabulary.
 *
 * @group micon_vocabulary
 */
class MiconVocabularyGenericTest extends GenericModuleTestBase {

  /**
   * {@inheritDoc}
   */
  protected function assertHookHelp(string $module): void {
    // Don't do anything here. Just overwrite this useless method, so we do
    // don't have to implement hook_help().
  }

}
