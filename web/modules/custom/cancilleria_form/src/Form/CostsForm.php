<?php

namespace Drupal\cancilleria_form\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CostsForm.
 */
class CostsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'costs_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['visa_type'] = [
      '#type' => 'select',
      '#options' => $this->getOptionsVisaType(),
      '#title' => $this->t('Visa Type'),
      '#required' => true,
      '#ajax' => [
        'callback' => [$this, 'callbackAjaxActivity'],
        'wrapper' => 'activity-wrapper'
      ],
    ];

    $form['activity'] = [
      '#type' => 'select',
      '#options' => [],
      '#title' => $this->t('Activity/occupation'),
      '#prefix' => '<div id="activity-wrapper">',
      '#suffix' => '</div>',
      '#empty_option' => $this->t('- Select -'),
      '#ajax' => [
        'callback' => [$this, 'callbackAjaxResult'],
        'wrapper' => 'result-wrapper'
      ],
    ];

    $form['result'] = [
      '#prefix' => '<div id="result-wrapper">',
      '#suffix' => '</div>',
      '#theme' => 'cancilleria_form',

    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
  }

  /**
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getOptionsVisaType() {
    $output = [];
    $entity = \Drupal::entityTypeManager()->getStorage('costs_means')->loadMultiple();

    foreach ($entity as $value) {
      $output[$value->field_visa_type->value] = $value->field_visa_type->value;
    }
    return $output;
  }

  /**
   * Callback AJAX Topic
   * @param array $form
   * @param FormStateInterface $form_state
   * @return AjaxResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function callbackAjaxActivity(array &$form, FormStateInterface $form_state) {
    $output[] = $this->t('- Select -');
    if ($form_state->getValue('visa_type')) {
      // Get values.
      $value = $form_state->getValue('visa_type');
      $entity = \Drupal::entityTypeManager()->getStorage('costs_means')->loadByProperties(['field_visa_type' => $value]);
      foreach ($entity as $value) {
        $output[$value->field_activity->value] = $value->field_activity->value;
      }

      $form['activity']['#options'] = $output;
    }
    $form_state->setRebuild(TRUE);
    $response = new AjaxResponse();


    $response->addCommand(new ReplaceCommand('#activity-wrapper', $form['activity']));
    return $response;
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return AjaxResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function callbackAjaxResult(array &$form, FormStateInterface $form_state) {

    if ($form_state->getValue('activity')) {
      // Get values.
      $value = $form_state->getValue('activity');

      $entity = \Drupal::entityTypeManager()->getStorage('costs_means')->loadByProperties(['field_activity' => $value]);
      $entity = reset($entity);

      $form['result']['#usd_visa'] = $entity->field_usd_visa->value;
      $form['result']['#usd_study'] = $entity->field_usd_study->value;
      $form['result']['#euro_study'] = $entity->field_euro_study->value;
      $form['result']['#euro_visa'] = $entity->field_euro_visa->value;
    }
    $form_state->setRebuild(TRUE);
    $response = new AjaxResponse();


    $response->addCommand(new ReplaceCommand('#result-wrapper', $form['result']));
    return $response;
  }

}
