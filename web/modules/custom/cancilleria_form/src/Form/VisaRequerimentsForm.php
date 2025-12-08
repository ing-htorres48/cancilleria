<?php

namespace Drupal\cancilleria_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VisaRequerimentsForm.
 */
class VisaRequerimentsForm extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'visa_requeriments_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['nationality'] = [
      '#type' => 'select',
      '#options' => $this->getTermOptions('nationality'),
      '#title' => $this->t('Nationality')
    ];

    $form['passport_type'] = [
      '#type' => 'select',
      '#options' => $this->getTermOptions('passport_type'),
      '#title' => $this->t('Passport type')
    ];

    $form['trip_purpose'] = [
      '#type' => 'select',
      '#options' => $this->getTermOptions('trip_purpose'),
      '#title' => $this->t('Trip purpose'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
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
    $result = $this->getRules(
      $form_state->getValue('nationality'),
      $form_state->getValue('passport_type'),
      $form_state->getValue('trip_purpose'));
    foreach ($result as  $value) {
      \Drupal::messenger()->addMessage($value);
    }
  }

  protected function getTermOptions(string $vocabulary) {
    $output = [];
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vocabulary);
    foreach ($term as $index => $item) {
      $output[$item->tid] = $item->name;
    }
    return $output;
  }

  protected function getRules($country, $passport_type, $trip) {
	  $output = [];
    $entity = $this->entityTypeManager->getStorage('visa_requirements')->loadByProperties([
        'field_nationality' => $country,
        'field_passport_type'=> $passport_type,
        'field_trip_purpose' => $trip
      ]
    );
    /*$entity = $this->entityTypeManager->getStorage('visa_requirements')->loadByProperties([
        'field_nationality' => $country,
      ]
    );*/
    foreach ($entity as $value) {
      $output[] = $value->field_answer->value;
    }
    return $output;

  }

}
