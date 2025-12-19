<?php

namespace Drupal\micon_linkit\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit\Plugin\Field\FieldWidget\LinkitWidget;
use Drupal\micon\MiconIconizeTrait;
use Drupal\micon_link\Plugin\Field\FieldWidget\MiconLinkWidgetTrait;

/**
 * Plugin implementation of the 'linkit' widget.
 *
 * @FieldWidget(
 *   id = "micon_linkit",
 *   label = @Translation("Linkit (with icon)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class MiconLinkitWidget extends LinkitWidget {

  use MiconIconizeTrait;
  use MiconLinkWidgetTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return self::prependDefaultSettings(parent::defaultSettings());
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
