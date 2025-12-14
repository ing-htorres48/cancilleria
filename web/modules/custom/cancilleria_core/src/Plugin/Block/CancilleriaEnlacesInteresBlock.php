<?php

namespace Drupal\cancilleria_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\Entity\Media;

/**
 * Provides a "Cancillería - Enlaces de Interés" block.
 *
 * @Block(
 *   id = "cancilleria_enlaces_interes_block",
 *   admin_label = @Translation("Cancillería - Enlaces de Interés"),
 *   category = @Translation("Cancillería")
 * )
 */
class CancilleriaEnlacesInteresBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'items' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Recuperar ítems existentes desde FormState o configuración.
    $items = $form_state->get('items');
    if ($items === NULL) {
      $items = $this->configuration['items'] ?? [];
      $form_state->set('items', $items);
    }

    $form['items'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Enlaces de interés'),
      '#tree' => TRUE,
    ];

    foreach ($items as $delta => $item) {
      $form['items'][$delta] = [
        '#type' => 'details',
        '#title' => $this->t('Ítem @num', ['@num' => $delta + 1]),
        '#open' => TRUE,
      ];

      // Imagen
      $form['items'][$delta]['media_id'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Imagen'),
        '#target_type' => 'media',
        '#selection_settings' => [
          'target_bundles' => ['image'],
        ],
        '#default_value' => !empty($item['media_id'])
          ? Media::load($item['media_id'])
          : NULL,
      ];

      // Enlace
      $form['items'][$delta]['url'] = [
        '#type' => 'url',
        '#title' => $this->t('Enlace'),
        '#default_value' => $item['url'] ?? '',
        '#required' => TRUE,
      ];

      // Descripción
      $form['items'][$delta]['descripcion'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Descripción'),
        '#default_value' => $item['descripcion'] ?? '',
        '#rows' => 3,
      ];
    }

    // Botón para agregar otro ítem.
    $form['add_item'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar otro'),
      '#submit' => [[get_class($this), 'addItem']],
      '#ajax' => [
        'callback' => [get_class($this), 'ajaxCallback'],
        'wrapper' => 'enlaces-interes-wrapper',
      ],
    ];

    // Wrapper AJAX.
    $form['#prefix'] = '<div id="enlaces-interes-wrapper">';
    $form['#suffix'] = '</div>';

    return $form;
  }

  /**
   * Submit handler para agregar un nuevo ítem.
   */
  public static function addItem(array &$form, FormStateInterface $form_state) {
    $items = $form_state->get('items') ?? [];
    $items[] = [
      'media_id' => NULL,
      'url' => '',
      'descripcion' => '',
    ];
    $form_state->set('items', $items);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Callback AJAX.
   */
  public static function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    // Guardar ítems del bloque.
    $this->configuration['items'] = array_values(
      array_filter($form_state->getValue('items') ?? [])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $items = [];

    foreach ($this->configuration['items'] as $item) {
      $image_url = NULL;

      if (!empty($item['media_id'])) {
        $media = Media::load($item['media_id']);
        if ($media && $media->hasField('field_media_image')) {
          $file = $media->field_media_image->entity;
          if ($file) {
            $image_url = \Drupal::service('file_url_generator')
              ->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      if ($image_url && !empty($item['url'])) {
        $items[] = [
          'image' => $image_url,
          'url' => $item['url'],
          'descripcion' => $item['descripcion'] ?? '',
        ];
      }
    }

    return [
      '#theme' => 'cancilleria_enlaces_interes_block',
      '#items' => $items,
      '#cache' => [
        'max-age' => 0,
      ],
       '#attached' => [
          'library' => [
            'cancilleria_core/cancilleria_enlaces_interes',
          ],
        ],
    ];
  }

}
