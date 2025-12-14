<?php

namespace Drupal\cnu_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\file\Entity\File;

/**
 * @Block(
 *   id = "items_c_incluyente_block",
 *   admin_label = @Translation("Items C Incluyente"),
 * )
 */
class ItemsCIncluyenteBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'items' => [],
    ];
  }

  /**
   * Load saved Paragraph entities.
   */
  private function loadParagraphs() {
    $ids = $this->configuration['items'] ?? [];
    return Paragraph::loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['items'] = [
      '#type' => 'entity_reference_revisions',
      '#title' => $this->t('Items (Paragraph item_block)'),
      '#target_type' => 'paragraph',
      '#default_value' => $this->loadParagraphs(),
      '#multiple' => TRUE,
      '#widget' => 'paragraphs',
      '#selection_settings' => [
        'target_bundles' => ['item_block'],
      ],
      '#description' => 'Agrega uno o más párrafos del tipo item_block.',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValue('items') ?? [];

    $ids = [];
    foreach ($values as $value) {
      if (!empty($value['entity'])) {
        $entity = $value['entity'];
        $entity->save();
        $ids[] = $entity->id();
      }
    }

    $this->configuration['items'] = $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $paragraphs = $this->loadParagraphs();
    $items = [];

    foreach ($paragraphs as $p) {
      $image_url = '';

      if ($p->get('field_imagen')->entity) {
        $file = $p->get('field_imagen')->entity;
        $image_url = file_create_url($file->getFileUri());
      }

      $items[] = [
        'title' => $p->get('field_titulo')->value ?? '',
        'desc'  => $p->get('field_descripcion')->value ?? '',
        'image' => $image_url,
      ];
    }

    return [
      '#theme' => 'items_c_incluyente',
      '#items' => $items,
      '#cache' => ['max-age' => 0],
    ];
  }

}
