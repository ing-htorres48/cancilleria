<?php

namespace Drupal\cancilleria_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a "Cancillería - Título de Página" block.
 *
 * @Block(
 *   id = "cancilleria_page_title_block",
 *   admin_label = @Translation("Cancillería - Título de Página"),
 *   category = @Translation("Cancillería")
 * )
 */
class CancilleriaPageTitleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'page_title' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['page_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título de la página'),
      '#default_value' => $this->configuration['page_title'],
      '#required' => TRUE,
      '#description' => $this->t('Texto que se mostrará como título principal de la página'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['page_title'] = $form_state->getValue('page_title');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'cancilleria_page_title_block',
      '#title' => $this->configuration['page_title'],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
