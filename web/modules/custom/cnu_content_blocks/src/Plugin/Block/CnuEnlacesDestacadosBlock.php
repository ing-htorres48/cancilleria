<?php

namespace Drupal\cnu_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a "CNU Enlaces Destacados" Block.
 *
 * @Block(
 *   id = "cnu_enlaces_destacados",
 *   admin_label = @Translation("CNU - Enlaces Destacados"),
 *   category = @Translation("ColombianosUNE - Blocks")
 * )
 */
class CnuEnlacesDestacadosBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entityTypeManager;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Número de enlaces a mostrar'),
      '#default_value' => $config['limit'] ?? 4,
      '#min' => 1,
      '#max' => 50,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('limit', $form_state->getValue('limit'));
  }

  public function build() {
    $config = $this->getConfiguration();
    $limit = $config['limit'] ?? 4;

    // Query para obtener nodos del tipo enlaces_detacados.
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'imagen_tramites_y_servicios')
      ->condition('status', 1)
      ->condition('field_destacado', 1) // 👈 SOLO los destacados
      ->sort('created', 'DESC')
      ->range(0, $limit)
      ->accessCheck(TRUE)
      ->execute();


    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
      $item = [
        'title' => $node->label(),
        'url' => '',
        'image' => '',
      ];


      // Enlace (field_link devuelve un objeto con ->uri y ->title)
      if ($node->hasField('field_enlace_tramite') && !$node->get('field_enlace_tramite')->isEmpty()) {
        $link_field = $node->get('field_enlace_tramite')->first();
        $item['url'] = $link_field->getUrl()->toString();
      }

      // Imagen
      if ($node->hasField('field_logo') && !$node->get('field_logo')->isEmpty()) {
        $file = $node->get('field_logo')->entity;
        if ($file) {
          $item['image'] = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());
        }
      }

      $items[] = $item;
    }

    return [
      '#theme' => 'cnu_enlaces_destacados',
      '#items' => $items,
      '#attached' => [
        'library' => [
          // Aquí puedes añadir la librería si necesitas JS o CSS.
        ],
      ],
    ];
  }
}
