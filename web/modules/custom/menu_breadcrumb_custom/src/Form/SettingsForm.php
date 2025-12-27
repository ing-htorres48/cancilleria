
<?php

declare(strict_types=1);

namespace Drupal\menu_breadcrumb_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config form for Menu Breadcrumb Custom.
 *
 * Drupal 11-safe: no custom constructor required.
 */
final class SettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames(): array {
    return ['menu_breadcrumb_custom.settings'];
  }

  public function getFormId(): string {
    return 'menu_breadcrumb_custom_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('menu_breadcrumb_custom.settings');

    $form['menu_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Menú fuente (machine name)'),
      '#default_value' => $config->get('menu_name') ?? 'main',
      '#description' => $this->t('Ejemplo: main'),
      '#required' => TRUE,
    ];

    $form['show_home'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar "Inicio"'),
      '#default_value' => (bool) $config->get('show_home'),
    ];

    $form['home_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta de "Inicio"'),
      '#default_value' => $config->get('home_label') ?? 'Inicio',
      '#states' => [
        'visible' => [
          ':input[name="show_home"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['always_show_current_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Fallback: siempre mostrar la página actual'),
      '#default_value' => (bool) $config->get('always_show_current_page'),
      '#description' => $this->t('Si la ruta no existe en el menú, se mostrará Inicio + Página actual.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('menu_breadcrumb_custom.settings')
      ->set('menu_name', (string) $form_state->getValue('menu_name'))
      ->set('show_home', (bool) $form_state->getValue('show_home'))
      ->set('home_label', (string) $form_state->getValue('home_label'))
      ->set('always_show_current_page', (bool) $form_state->getValue('always_show_current_page'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
