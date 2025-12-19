<?php

namespace Drupal\micon\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'string_micon' field type.
 *
 * @FieldType(
 *   id = "string_micon",
 *   label = @Translation("Icon"),
 *   description = @Translation("A field containing an icon."),
 *   default_widget = "string_micon",
 *   default_formatter = "string_micon"
 * )
 */
class StringMiconItem extends StringItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    // We don't want to set any default storage settings here, as we are setting
    // them manually in the schema definition:
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        // Set these, as the hard defaults used by the parent class. We don't
        // want these to be configurable:
        'value' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    if ($value) {
      $value = \Drupal::service('micon.icon.manager')->getIconMatch($value);
    }
    return $value === NULL || $value === '';
  }

}
