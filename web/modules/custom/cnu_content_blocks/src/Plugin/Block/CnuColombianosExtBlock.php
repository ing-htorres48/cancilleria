<?php

namespace Drupal\cnu_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides "CNU - Colombianos Ext" block.
 *
 * @Block(
 *   id = "cnu_colombianos_ext",
 *   admin_label = @Translation("CNU - Colombianos Ext"),
 *   category = @Translation("ColombianosUNE - Blocks"),
 * )
 */
class CnuColombianosExtBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entityTypeManager;
  protected $dateFormatter;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
    );
  }

  /**
   * Formulario del bloque (cantidad a mostrar).
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Número de perfiles a mostrar'),
      '#default_value' => $config['limit'] ?? 3,
      '#min' => 1,
      '#max' => 20,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('limit', $form_state->getValue('limit'));
  }

  /**
   * Build del bloque.
   */
  public function build() {
    $config = $this->getConfiguration();
    $limit = $config['limit'] ?? 3;

    // Query: últimos N colombianos en el exterior.
    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'colombiano_en_el_exterior')
      ->sort('created', 'DESC')
      ->range(0, $limit)
      ->accessCheck(TRUE)
      ->execute();

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    $items = [];

    foreach ($nodes as $node) {
      $item = [
        'title'   => $node->label(),
        'url'     => $node->toUrl()->toString(),
        'summary' => '',
        'date'    => $this->dateFormatter->format($node->getCreatedTime(), 'custom', 'F d, Y'),
        'image'   => '',
      ];

      // Descripción
      if ($node->hasField('field_descripcion_colombiano') && !$node->get('field_descripcion_colombiano')->isEmpty()) {
        $full_text = $node->get('field_descripcion_colombiano')->value;

        // Cortar a 142 caracteres respetando acentos y sin cortar palabras.
        $summary = mb_substr($full_text, 0, 130);

        // Opcional: agregar "…" si el texto fue truncado
        if (mb_strlen($full_text) > 142) {
          $summary .= '…';
        }

        $item['summary'] = $summary;
      }


      // Foto
      if ($node->hasField('field_foto') && !$node->get('field_foto')->isEmpty()) {
        $file = $node->get('field_foto')->entity;
        if ($file) {
          $item['image'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());

        }
      }

      $items[] = $item;
    }

    return [
      '#theme' => 'cnu_colombianos_ext',
      '#items' => $items,
      '#attached' => [
        'library' => ['cnu_content_blocks/cnu_content_blocks'],
      ],
    ];
  }

  public function defaultConfiguration() {
    return [
      'limit' => 3,
    ];
  }
}
