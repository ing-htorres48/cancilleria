<?php

namespace Drupal\micon_link\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Drupal\micon\MiconIconizeTrait;

/**
 * Plugin implementation of the 'link' widget.
 *
 * @FieldWidget(
 *   id = "micon_link",
 *   label = @Translation("Link (with icon)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class MiconLinkWidget extends LinkWidget {

  use MiconIconizeTrait;
  use MiconLinkWidgetTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'packages' => [],
      'icon' => '',
      'position' => FALSE,
      'target' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    return $this->getSettingsFormElement($element);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    return $this->getFormElement($items, $delta, $element, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->appendSettingsSummary(parent::settingsSummary());
  }

}
