<?php

namespace Drupal\cancilleria_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\Entity\Media;

/**
 * Provides a "Cancillería - Redes Sociales" block.
 *
 * @Block(
 *   id = "cancilleria_social_block",
 *   admin_label = @Translation("Cancillería - Redes Sociales"),
 *   category = @Translation("Cancillería")
 * )
 */
class CancilleriaSocialBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'facebook_media' => NULL,
      'facebook_url' => '',
      'x_media' => NULL,
      'x_url' => '',
      'instagram_media' => NULL,
      'instagram_url' => '',
      'youtube_media' => NULL,
      'youtube_url' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Facebook
    $form['facebook'] = [
      '#type' => 'details',
      '#title' => $this->t('Facebook'),
      '#open' => TRUE,
    ];
    $form['facebook']['facebook_media'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Logo Facebook'),
      '#target_type' => 'media',
      '#selection_settings' => [
        'target_bundles' => ['image'],
      ],
      '#default_value' => $this->configuration['facebook_media']
        ? Media::load($this->configuration['facebook_media'])
        : NULL,
    ];
    $form['facebook']['facebook_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL Facebook'),
      '#default_value' => $this->configuration['facebook_url'],
    ];

    // X
    $form['x'] = [
      '#type' => 'details',
      '#title' => $this->t('X'),
      '#open' => TRUE,
    ];
    $form['x']['x_media'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Logo X'),
      '#target_type' => 'media',
      '#selection_settings' => [
        'target_bundles' => ['image'],
      ],
      '#default_value' => $this->configuration['x_media']
        ? Media::load($this->configuration['x_media'])
        : NULL,
    ];
    $form['x']['x_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL X'),
      '#default_value' => $this->configuration['x_url'],
    ];

    // Instagram
    $form['instagram'] = [
      '#type' => 'details',
      '#title' => $this->t('Instagram'),
      '#open' => TRUE,
    ];
    $form['instagram']['instagram_media'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Logo Instagram'),
      '#target_type' => 'media',
      '#selection_settings' => [
        'target_bundles' => ['image'],
      ],
      '#default_value' => $this->configuration['instagram_media']
        ? Media::load($this->configuration['instagram_media'])
        : NULL,
    ];
    $form['instagram']['instagram_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL Instagram'),
      '#default_value' => $this->configuration['instagram_url'],
    ];

    // YouTube
    $form['youtube'] = [
      '#type' => 'details',
      '#title' => $this->t('YouTube'),
      '#open' => TRUE,
    ];
    $form['youtube']['youtube_media'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Logo YouTube'),
      '#target_type' => 'media',
      '#selection_settings' => [
        'target_bundles' => ['image'],
      ],
      '#default_value' => $this->configuration['youtube_media']
        ? Media::load($this->configuration['youtube_media'])
        : NULL,
    ];
    $form['youtube']['youtube_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL YouTube'),
      '#default_value' => $this->configuration['youtube_url'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
  parent::blockSubmit($form, $form_state);

  // Facebook
  $this->configuration['facebook_media'] = $form_state->getValue(['facebook', 'facebook_media']);
  $this->configuration['facebook_url'] = $form_state->getValue(['facebook', 'facebook_url']);

  // X
  $this->configuration['x_media'] = $form_state->getValue(['x', 'x_media']);
  $this->configuration['x_url'] = $form_state->getValue(['x', 'x_url']);

  // Instagram
  $this->configuration['instagram_media'] = $form_state->getValue(['instagram', 'instagram_media']);
  $this->configuration['instagram_url'] = $form_state->getValue(['instagram', 'instagram_url']);

  // YouTube
  $this->configuration['youtube_media'] = $form_state->getValue(['youtube', 'youtube_media']);
  $this->configuration['youtube_url'] = $form_state->getValue(['youtube', 'youtube_url']);
}


  /**
   * {@inheritdoc}
   */
  public function build() {
    $items = [];

    $networks = [
      'facebook' => 'Facebook',
      'x' => 'X',
      'instagram' => 'Instagram',
      'youtube' => 'YouTube',
    ];

    foreach ($networks as $key => $label) {
      $media_id = $this->configuration[$key . '_media'];
      $url = $this->configuration[$key . '_url'];

      if ($media_id && $url) {
        $media = Media::load($media_id);
        if ($media && $media->hasField('field_media_image')) {
          $file = $media->field_media_image->entity;
          if ($file) {
            $items[] = [
              'url' => $url,
              'image' => \Drupal::service('file_url_generator')
                ->generateAbsoluteString($file->getFileUri()),
              'alt' => $label,
            ];
          }
        }
      }
    }

    return [
      '#theme' => 'cancilleria_social_block',
      '#items' => $items,
      '#attached' => [
        'library' => [
          'cancilleria_core/cancilleria_social_block',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

}
