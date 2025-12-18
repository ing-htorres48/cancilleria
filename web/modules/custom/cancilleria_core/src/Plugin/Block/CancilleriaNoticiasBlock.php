<?php

namespace Drupal\cancilleria_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
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
  protected PagerManagerInterface $pagerManager;
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter,
    PagerManagerInterface $pager_manager,
    FileUrlGeneratorInterface $file_url_generator
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->pagerManager = $pager_manager;
    $this->fileUrlGenerator = $file_url_generator;
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('pager.manager'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {

    $limit = 8;

    // Query con paginación real (igual que Views).
    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'news')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->pager($limit)
      ->accessCheck(TRUE);

    $nids = $query->execute();

    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nids);

    $news = [];

    foreach ($nodes as $node) {
      $image_url = NULL;

      if (
        $node->hasField('field_imagen_news_thumb') &&
        !$node->get('field_imagen_news_thumb')->isEmpty()
      ) {
        $file = $node->get('field_imagen_news_thumb')->entity;
        if ($file) {
          $image_url = $this->fileUrlGenerator
            ->generateAbsoluteString($file->getFileUri());
        }
      }

      $news[] = [
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
        'date' => $this->dateFormatter->format(
          $node->getCreatedTime(),
          'custom',
          'd M Y'
        ),
        'summary' => $node->hasField('body')
          ? text_summary($node->get('body')->value, NULL, 160)
          : '',
        'image' => $image_url,
      ];
    }

    return [
      '#theme' => 'cancilleria_noticias_block',
      '#news' => $news,
      '#pager' => [
        '#type' => 'pager',
      ],
      '#cache' => [
        'tags' => ['node_list:news'],
        'contexts' => ['url.query_args:page'],
      ],
      '#attached' => [
        'library' => [
          'cancilleria_core/cancilleria_home_noticias',
        ],
      ],
    ];
  }

}
