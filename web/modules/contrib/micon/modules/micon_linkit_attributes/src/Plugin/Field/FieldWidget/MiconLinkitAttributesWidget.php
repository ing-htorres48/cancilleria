<?php

namespace Drupal\micon_linkit_attributes\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit_attributes\Plugin\Field\FieldWidget\LinkitWithAttributesWidget;
use Drupal\micon\MiconIconizeTrait;
use Drupal\micon_link\Plugin\Field\FieldWidget\MiconLinkWidgetTrait;

/**
 * Plugin implementation of the 'linkit_attributes' widget.
 *
 * @FieldWidget(
 *   id = "micon_linkit_attributes",
 *   label = @Translation("Linkit (with icon and attributes)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class MiconLinkitAttributesWidget extends LinkitWithAttributesWidget {

  use MiconIconizeTrait;
  use MiconLinkWidgetTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaultSettings = parent::defaultSettings();
    $miconDefaultSettings = self::prependDefaultSettings();
    // "target" is already part of the linkit_attributes widget, unset it on
    // the micon default settings:
    unset($miconDefaultSettings['target']);
    return $defaultSettings + $miconDefaultSettings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $miconElement = $this->getSettingsFormElement();
    // "target" is already part of the linkit_attributes widget, unset it on
    // the micon element:
    unset($miconElement['target']);
    return $element + $miconElement;
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
    $sumamry = parent::settingsSummary();
    return $this->appendSettingsSummary($sumamry);
  }

}
