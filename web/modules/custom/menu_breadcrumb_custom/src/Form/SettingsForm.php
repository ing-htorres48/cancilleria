<?php

declare(strict_types=1);

namespace Drupal\menu_breadcrumb_custom\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class SettingsForm extends ConfigFormBase {

  public function __construct(
    ConfigFactoryInterface $config_factory,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($config_factory);
  }

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
    );
  }

  protected function getEditableConfigNames(): array {
    return ['menu_breadcrumb_custom.settings'];
  }

  public function getFormId(): string {
    return 'menu_breadcrumb_custom_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('menu_breadcrumb_custom.settings');

    $menus = $this->entityTypeManager->getStorage('menu')->loadMultiple();
    $options = [];
    foreach ($menus as $menu) {
      $options[$menu->id()] = $menu->label() . ' (' . $menu->id() . ')';
    }

    $form['menu_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Menú fuente del breadcrumb'),
      '#options' => $options,
      '#default_value' => $config->get('menu_name') ?: 'main',
      '#required' => TRUE,
    ];

    $form['show_home'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar "Inicio"'),
      '#default_value' => (bool) $config->get('show_home'),
    ];

    $form['home_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta de Inicio'),
      '#default_value' => (string) ($config->get('home_label') ?: 'Inicio'),
      '#states' => [
        'visible' => [
          ':input[name="show_home"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['always_show_current_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Siempre mostrar la página actual (fallback: Inicio + Página actual)'),
      '#default_value' => (bool) $config->get('always_show_current_page'),
      '#description' => $this->t('Si no se encuentra la página en el menú, se mostrará Inicio + Página actual.'),
    ];

    $form['hide_on_front'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ocultar en portada'),
      '#default_value' => (bool) $config->get('hide_on_front'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('menu_breadcrumb_custom.settings')
      ->set('menu_name', (string) $form_state->getValue('menu_name'))
      ->set('show_home', (bool) $form_state->getValue('show_home'))
      ->set('home_label', (string) $form_state->getValue('home_label'))
      ->set('always_show_current_page', (bool) $form_state->getValue('always_show_current_page'))
      ->set('hide_on_front', (bool) $form_state->getValue('hide_on_front'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
