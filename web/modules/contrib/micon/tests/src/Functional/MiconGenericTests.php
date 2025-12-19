<?php

namespace Drupal\Tests\micon\Functional;

use Drupal\Tests\system\Functional\Module\GenericModuleTestBase;

/**
 * Generic module test for micon.
 *
 * @group micon
 */
class MiconGenericTests extends GenericModuleTestBase {

  /**
   * {@inheritDoc}
   */
  protected static $modules = [
    'node',
    'path',
    'micon_basic_configs',
    'micon_config_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createContentType([
      'type' => 'page',
      'name' => 'Basic page',
    ]);
  }

  /**
   * {@inheritDoc}
   */
  protected function assertHookHelp(string $module): void {
    // Don't do anything here. Just overwrite this useless method, so we don't
    // have to implement hook_help().
  }

  /**
   * {@inheritDoc}
   */
  protected function preUnInstallSteps(): void {
    // Programmatically delete the field storage, so we can uninstall the
    // module:
    $configFactory = \Drupal::service('config.factory');
    $configFactory->getEditable('core.entity_form_display.node.article.default')->delete();
    $configFactory->getEditable('core.entity_view_display.node.article.default')->delete();
    $configFactory->getEditable('field.field.node.article.field_icon')->delete();
    $configFactory->getEditable('field.storage.node.field_icon')->delete();
    $configFactory->getEditable('node.type.article.yml')->delete();
  }

}
