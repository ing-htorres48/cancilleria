<?php

declare(strict_types=1);

namespace Drupal\menu_breadcrumb_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
      '#title' => $this->t('Menú (machine name)'),
      '#default_value' => $config->get('menu_name') ?? 'main',
      '#required' => TRUE,
    ];

    $form['show_home'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Inicio'),
      '#default_value' => $config->get('show_home'),
    ];

    $form['home_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta Inicio'),
      '#default_value' => $config->get('home_label'),
    ];

    $form['always_show_current_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Siempre mostrar página actual'),
      '#default_value' => $config->get('always_show_current_page'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('menu_breadcrumb_custom.settings')
      ->set('menu_name', $form_state->getValue('menu_name'))
      ->set('show_home', (bool) $form_state->getValue('show_home'))
      ->set('home_label', $form_state->getValue('home_label'))
      ->set('always_show_current_page', (bool) $form_state->getValue('always_show_current_page'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
