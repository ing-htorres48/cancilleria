<?php

namespace Drupal\micon\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\micon\MiconIconManager;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'string_micon' formatter.
 *
 * @FieldFormatter(
 *   id = "string_micon",
 *   label = @Translation("Icon"),
 *   field_types = {
 *     "string_micon"
 *   }
 * )
 */
class StringMiconFormatter extends FormatterBase {

  /**
   * The micon icon manager.
   *
   * @var \Drupal\micon\MiconIconManager
   */
  protected $miconIconManager;

  /**
   * Constructs a StringMiconFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\micon\MiconIconManager $miconIconManager
   *   The micon icon manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, MiconIconManager $miconIconManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->miconIconManager = $miconIconManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @see Drupal\Core\Field\FormatterBase::createInstance().
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('micon.icon.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if ($icon = $this->viewIcon($item)) {
        $elements[$delta] = $icon->toRenderable();
      }
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return \Drupal\micon\MiconIcon|null
   *   The Micon icon matching the icon_id.
   */
  protected function viewIcon(FieldItemInterface $item) {
    $icon_id = nl2br(Html::escape($item->value));
    return $this->miconIconManager->getIconMatch($icon_id);
  }

}
