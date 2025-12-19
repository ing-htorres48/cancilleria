<?php

namespace Drupal\micon_link\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\micon\Entity\Micon;

/**
 * Micon Link Widget Trait for Link Widgets.
 *
 * @package Drupal\micon_link\Plugin\Field\FieldWidget
 */
trait MiconLinkWidgetTrait {

  /**
   * {@inheritdoc}
   */
  public static function prependDefaultSettings(array $defaultSettings = []) {
    return [
      'placeholder_url' => '',
      'placeholder_title' => '',
      'target' => FALSE,
      'packages' => [],
      'icon' => '',
      'position' => FALSE,
    ] + $defaultSettings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsFormElement(array $element = []) {
    $element['packages'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Icon Packages'),
      '#default_value' => $this->getSetting('packages'),
      '#description' => $this->t('The icon packages that should be made available in this field. If no packages are selected, all will be made available.'),
      '#options' => Micon::loadActiveLabels(),
    ];

    $element['icon'] = [
      '#type' => 'micon',
      '#title' => $this->t('Default Icon'),
      '#default_value' => $this->getSetting('icon'),
    ];

    $element['position'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow icon position selection'),
      '#description' => $this->t('If selected, a "position" select will be made available.'),
      '#default_value' => $this->getSetting('position'),
    ];

    $element['target'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow target selection'),
      '#description' => $this->t('If selected, an "open in new window" checkbox will be made available.'),
      '#default_value' => $this->getSetting('target'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#element_validate'][] = [get_called_class(), 'validateElement'];

    $id = Html::getUniqueId('micon-link-' . $this->fieldDefinition->getName() . '-icon');

    $item = $items[$delta];
    $options = $item->get('options')->getValue();
    $attributes = $options['attributes'] ?? [];

    $element['options']['attributes']['data-icon'] = [
      '#type' => 'micon',
      '#title' => $this->t('Icon'),
      '#id' => $id,
      '#default_value' => $attributes['data-icon'] ?? $this->getSetting('icon'),
      '#packages' => $this->getPackages(),
    ];

    if ($this->getSetting('position')) {
      $element['options']['attributes']['data-icon-position'] = [
        '#type' => 'select',
        '#title' => $this->t('Icon position'),
        '#options' => [
          'before' => $this->t('Before'),
          'after' => $this->t('After'),
          'icon_only' => $this->t('Icon only'),
        ],
        '#default_value' => $attributes['data-icon-position'] ?? NULL,
        '#states' => [
          'invisible' => [
            '#' . $id => ['value' => ''],
          ],
          'optional' => [
            '#' . $id => ['value' => ''],
          ],
        ],
      ];
    }

    if ($this->getSetting('target')) {
      $element['options']['attributes']['target'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Open link in new window'),
        '#description' => $this->t('If selected, the menu link will open in a new window/tab when clicked.'),
        '#default_value' => !empty($attributes['target']),
        '#return_value' => '_blank',
      ];
    }

    return $element;
  }

  /**
   * Get packages available to this field.
   */
  protected function getPackages() {
    return $this->getSetting('packages');
  }

  /**
   * Recursively clean up options array if no data-icon is set.
   */
  public static function validateElement($element, FormStateInterface $form_state, $form) {
    $values = $form_state->getValue($element['#parents']);
    if (!empty($values)) {
      foreach ($values['options']['attributes'] as $attribute => $value) {
        if (!empty($value)) {
          $values['options']['attributes'][$attribute] = $value;
          $values['attributes'][$attribute] = $value;
        }
        else {
          unset($values['options']['attributes'][$attribute]);
          unset($values['attributes'][$attribute]);
        }
      }
    }
    $form_state->setValueForElement($element, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function appendSettingsSummary(array $summary = []) {
    $enabled_packages = array_filter($this->getSetting('packages'));
    if ($enabled_packages) {
      $enabled_packages = array_intersect_key(Micon::loadActiveLabels(), $enabled_packages);
      $summary[] = $this->t('With icon packages: @packages', [
        '@packages' => implode(', ', $enabled_packages),
      ]);
    }
    else {
      $summary[] = $this->t('With icon packages: @packages', ['@packages' => 'All']);
    }
    if ($icon = $this->getSetting('icon')) {
      $summary[] = $this->micon('Default icon: ')
        ->setIcon($icon)
        ->setIconAfter();
    }
    if ($this->getSetting('target')) {
      $summary[] = $this->t('Allow target selection');
    }
    return $summary;
  }

}
