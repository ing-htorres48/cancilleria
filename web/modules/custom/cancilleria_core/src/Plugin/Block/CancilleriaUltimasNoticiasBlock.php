<?php

namespace Drupal\cancilleria_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a "Cancillería - Últimas Noticias" block.
 *
 * @Block(
 *   id = "cancilleria_ultimas_noticias_block",
 *   admin_label = @Translation("Cancillería - Últimas Noticias"),
 *   category = @Translation("Cancillería")
 * )
 */
class CancilleriaUltimasNoticiasBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
      ->range(0, 2)
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

    /* =====================
     * VIDEO MULTIMEDIA HOME
     * ===================== */
    $video_nids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'video_multimedia_yt')
      ->condition('status', 1)
      ->condition('field_destacado_home', 1)
      ->sort('created', 'DESC')
      ->range(0, 1)
      ->accessCheck(TRUE)
      ->execute();

    $video = NULL;

    if (!empty($video_nids)) {
      $video_node = $this->entityTypeManager
        ->getStorage('node')
        ->load(reset($video_nids));

      if ($video_node && $video_node->hasField('field_video') && !$video_node->get('field_video')->isEmpty()) {
        $video = [
          'title' => $video_node->label(),
          'url' => $video_node->toUrl()->toString(),
          'video' => $video_node->get('field_video')->view([
            'label' => 'hidden',
            'type' => 'youtube_video',
          ]),
        ];
      }
    }

    return [
      '#theme' => 'cancilleria_ultimas_noticias_block',
      '#news' => $news,
      '#video' => $video,
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
