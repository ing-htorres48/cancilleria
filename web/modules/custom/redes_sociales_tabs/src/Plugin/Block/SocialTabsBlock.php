<?php

namespace Drupal\redes_sociales_tabs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Proporciona un bloque de pestañas de redes sociales.
 *
 * @Block(
 *   id = "social_tabs_block",
 *   admin_label = @Translation("Redes sociales (Facebook / Instagram / X / TikTok)"),
 * )
 */
class SocialTabsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'facebook_url' => 'https://www.facebook.com/CancilleriaCol/',
      'instagram_url' => 'https://www.instagram.com/cancilleriacol/',
      'twitter_url' => 'https://twitter.com/CancilleriaCol',
      'tiktok_url' => 'https://www.tiktok.com/@cancilleriacol',
      'enable_facebook' => TRUE,
      'enable_instagram' => TRUE,
      'enable_twitter' => TRUE,
      'enable_tiktok' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->configuration;

    $form['social_networks'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configuración de Redes Sociales'),
      '#description' => $this->t('Configura las URLs y visibilidad de cada red social.'),
    ];

    // Facebook
    $form['social_networks']['facebook_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Facebook'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['social_networks']['facebook_settings']['enable_facebook'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Habilitar Facebook'),
      '#default_value' => $config['enable_facebook'],
    ];

    $form['social_networks']['facebook_settings']['facebook_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL de página de Facebook'),
      '#default_value' => $config['facebook_url'],
      '#required' => $config['enable_facebook'],
      '#description' => $this->t('Ejemplo: https://www.facebook.com/tuPagina/'),
    ];

    // Instagram
    $form['social_networks']['instagram_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Instagram'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['social_networks']['instagram_settings']['enable_instagram'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Habilitar Instagram'),
      '#default_value' => $config['enable_instagram'],
    ];

    $form['social_networks']['instagram_settings']['instagram_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL de perfil de Instagram'),
      '#default_value' => $config['instagram_url'],
      '#required' => $config['enable_instagram'],
      '#description' => $this->t('Ejemplo: https://www.instagram.com/tuPerfil/'),
    ];

    // Twitter/X
    $form['social_networks']['twitter_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('X (Twitter)'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['social_networks']['twitter_settings']['enable_twitter'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Habilitar X'),
      '#default_value' => $config['enable_twitter'],
    ];

    $form['social_networks']['twitter_settings']['twitter_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL del perfil de X'),
      '#default_value' => $config['twitter_url'],
      '#required' => $config['enable_twitter'],
      '#description' => $this->t('Ejemplo: https://twitter.com/tuUsuario'),
    ];

    // TikTok
    $form['social_networks']['tiktok_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('TikTok'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['social_networks']['tiktok_settings']['enable_tiktok'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Habilitar TikTok'),
      '#default_value' => $config['enable_tiktok'],
    ];

    $form['social_networks']['tiktok_settings']['tiktok_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL del perfil de TikTok'),
      '#default_value' => $config['tiktok_url'],
      '#required' => $config['enable_tiktok'],
      '#description' => $this->t('Ejemplo: https://www.tiktok.com/@tuUsuario'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['facebook_url'] = $values['social_networks']['facebook_settings']['facebook_url'];
    $this->configuration['enable_facebook'] = $values['social_networks']['facebook_settings']['enable_facebook'];

    $this->configuration['instagram_url'] = $values['social_networks']['instagram_settings']['instagram_url'];
    $this->configuration['enable_instagram'] = $values['social_networks']['instagram_settings']['enable_instagram'];

    $this->configuration['twitter_url'] = $values['social_networks']['twitter_settings']['twitter_url'];
    $this->configuration['enable_twitter'] = $values['social_networks']['twitter_settings']['enable_twitter'];

    $this->configuration['tiktok_url'] = $values['social_networks']['tiktok_settings']['tiktok_url'];
    $this->configuration['enable_tiktok'] = $values['social_networks']['tiktok_settings']['enable_tiktok'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configuration;

    $build = [
      '#theme' => 'social_tabs_block',
      '#attributes' => [
        'class' => ['social-tabs-block'],
      ],
      '#facebook_url' => $config['facebook_url'],
      '#enable_facebook' => $config['enable_facebook'],
      '#instagram_url' => $config['instagram_url'],
      '#enable_instagram' => $config['enable_instagram'],
      '#twitter_url' => $config['twitter_url'],
      '#enable_twitter' => $config['enable_twitter'],
      '#tiktok_url' => $config['tiktok_url'],
      '#enable_tiktok' => $config['enable_tiktok'],
      '#attached' => [
        'library' => [
          'redes_sociales_tabs/social_tabs',
        ],
      ],
    ];

    return $build;
  }

}
