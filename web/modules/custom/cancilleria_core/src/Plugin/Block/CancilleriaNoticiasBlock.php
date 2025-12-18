<?php

namespace Drupal\cancilleria_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a "Cancillería - Noticias" block.
 *
 * @Block(
 *   id = "cancilleria_noticias_block",
 *   admin_label = @Translation("Cancillería - Noticias"),
 *   category = @Translation("Cancillería")
 * )
 */
class CancilleriaNoticiasBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected EntityTypeManagerInterface $entityTypeManager;
  protected DateFormatterInterface $dateFormatter;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    /* =====================
     * ÚLTIMAS 2 NOTICIAS
     * ===================== */
    $news_nids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'news')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE)
      ->execute();

    $news_nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($news_nids);

    $news = [];

    foreach ($news_nodes as $node) {
      $image_url = NULL;

      if (
        $node->hasField('field_imagen_news_thumb') &&
        !$node->get('field_imagen_news_thumb')->isEmpty()
      ) {
        $file = $node->get('field_imagen_news_thumb')->entity;
        if ($file) {
          $image_url = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());
        }
      }

      $news[] = [
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
        'date' => $this->dateFormatter->format(
          $node->getCreatedTime(),
          'custom',
          'M d, Y'
        ),
        'image' => $image_url,
      ];
    }


    return [
      '#theme' => 'cancilleria_ultimas_noticias_block',
      '#news' => $news,
      '#cache' => [
        'tags' => ['node_list'],
      ],
      '#attached' => [
        'library' => [
          'cancilleria_core/cancilleria_home_noticias',
        ],
      ],
      
    
    ];
  }

}
