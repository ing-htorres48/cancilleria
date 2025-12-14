<?php

namespace Drupal\cancilleria_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\Entity\Media;

/**
 * Provides a "Cancillería - Imagen (Media)" block.
 *
 * @Block(
 *   id = "cancilleria_image_block",
 *   admin_label = @Translation("Cancillería - Imagen (Media)"),
 *   category = @Translation("Cancillería")
 * )
 */
class CancilleriaImageBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'media_id' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['media_id'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Imagen'),
      '#target_type' => 'media',
      '#selection_settings' => [
        'target_bundles' => ['image'],
      ],
      '#default_value' => $this->configuration['media_id']
        ? Media::load($this->configuration['media_id'])
        : NULL,
      '#description' => $this->t('Selecciona una imagen desde la biblioteca de medios'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['media_id'] = $form_state->getValue('media_id');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $image_url = NULL;
    $alt = NULL;

    if (!empty($this->configuration['media_id'])) {
      $media = Media::load($this->configuration['media_id']);

      if ($media && $media->hasField('field_media_image')) {
        $image = $media->field_media_image->entity;

        if ($image) {
          $image_url = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($image->getFileUri());
          $alt = $media->field_media_image->alt;
        }
      }
    }

    return [
      '#theme' => 'cancilleria_image_block',
      '#image_url' => $image_url,
      '#alt' => $alt,
      '#attached' => [
        'library' => [
          'cancilleria_core/cancilleria_image_block',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
