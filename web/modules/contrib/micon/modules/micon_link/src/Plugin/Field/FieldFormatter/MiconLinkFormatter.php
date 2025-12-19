<?php

namespace Drupal\micon_link\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Utility\Token;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;
use Drupal\micon\MiconIconizeTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'micon_link' formatter.
 *
 * @FieldFormatter(
 *   id = "micon_link",
 *   label = @Translation("Link (with icon)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class MiconLinkFormatter extends LinkFormatter {

  use MiconIconizeTrait;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, PathValidatorInterface $path_validator, Token $token) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $path_validator);
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('path.validator'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'title' => '',
      'icon' => '',
      'position' => 'before',
      'text_only' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $settings = $this->getSettings();

    if (!empty($settings['title'])) {
      $summary[] = $this->t('Link title as @title', ['@title' => $settings['title']]);
    }
    if (!empty($settings['icon'])) {
      $summary[] = $this->micon('Icon as')->setIcon($settings['icon'])->setIconAfter();
    }
    if (!empty($settings['position'])) {
      $summary[] = $this->t('Icon position: @value', ['@value' => Unicode::ucfirst($settings['position'])]);
    }
    if (!empty($settings['trim_length'])) {
      $summary[] = $this->t('Link text trimmed to @limit characters', ['@limit' => $settings['trim_length']]);
    }
    else {
      $summary[] = $this->t('Link text not trimmed');
    }

    if (!empty($settings['text_only'])) {
      $summary[] = $this->t('Text only');
    }
    else {
      if (!empty($settings['rel'])) {
        $summary[] = $this->t('Add rel="@rel"', ['@rel' => $settings['rel']]);
      }
      if (!empty($settings['target'])) {
        $summary[] = $this->t('Open link in new window');
      }
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#default_value' => $this->getSetting('title'),
      '#description' => $this->t('Will be used as the link title unless one has been set on the field. Supports token replacement.'),
      '#weight' => -10,
    ];
    $elements['text_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Text only'),
      '#default_value' => $this->getSetting('text_only'),
      '#weight' => -10,
    ];
    $elements['icon'] = [
      '#type' => 'micon',
      '#title' => $this->t('Link icon Fallback'),
      '#default_value' => $this->getSetting('icon'),
      '#description' => $this->t('Will be used as the link icon as a fallback, if no icon was specified on the field.'),
      '#weight' => -10,
    ];
    $elements['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Icon position'),
      '#options' => [
        'before' => $this->t('Before'),
        'after' => $this->t('After'),
        'icon_only' => $this->t('Icon only'),
      ],
      '#default_value' => $this->getSetting('position'),
      '#required' => TRUE,
      '#weight' => -10,
    ];

    $visibility = [
      'invisible' => [
        ':input[name*="text_only"]' => ['checked' => TRUE],
      ],
    ];
    $elements['rel']['#states'] = $visibility;
    $elements['target']['#states'] = $visibility;

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = parent::viewElements($items, $langcode);
    $entity = $items->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $title = $this->getSetting('title');
    $text_only = $this->getSetting('text_only');
    foreach ($element as &$item) {
      if (!isset($item['#url']) || empty($item['#url'])) {
        // The current field item is malformed and doesn't have an url object,
        // or the url object is empty. Continue with the next element:
        continue;
      }
      if ($title && (empty($item['#title']) || $item['#title'] === $item['#url']->toString())) {
        $item['#title'] = $this->token->replace($title, [$entity_type => $entity]);
      }
      /** @var \Drupal\Core\Url $urlObject */
      $urlObject = $item['#url'];
      $urlObjectAttributes = $urlObject->getOption('attributes');
      $icon = !empty($urlObjectAttributes['data-icon']) ? $urlObjectAttributes['data-icon'] : $this->getSetting('icon');
      $iconPosition = !empty($urlObjectAttributes['data-icon-position']) ? $urlObjectAttributes['data-icon-position'] : $this->getSetting('position');
      if ($icon) {
        $micon = $this->micon($item['#title']);
        // We ALWAYS have to set the icon, otherwise the render function
        // in MiconIconize is not acknowledging any of our set parameters.
        $micon->setIcon($icon);
        // Modify the icon position if "after" or "icon_only":
        switch ($iconPosition) {
          // Display after label:
          case 'after':
            $micon->setIconAfter();
            break;

          // Display icon only:
          case 'icon_only':
            $micon->setIconOnly(TRUE);
            break;
        }
        $item['#title'] = $micon;
      }
      if ($text_only) {
        $item = [
          '#markup' => $item['#title'],
        ];
      }
    }
    return $element;
  }

}
