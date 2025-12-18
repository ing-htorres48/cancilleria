<?php

namespace Drupal\cnu_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CNU Content Prominent Block' Block.
 *
 * @Block(
 *   id = "cnu_content_prominent_block",
 *   admin_label = @Translation("CNU Content Prominent Block"),
 *   category = @Translation("ColombianosUNE - Blocks"),
 * )
 */
class CnuContentProminentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The default limit for the number of content items to display.
   *
   * @var int
   */
  public $limit = 5;

  /**
   * Constructs a new CnuContentProminentBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    PathMatcherInterface $path_matcher,
    DateFormatterInterface $date_formatter,
    RendererInterface $renderer,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->pathMatcher = $path_matcher;
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('path.matcher'),
      $container->get('date.formatter'),
      $container->get('renderer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm($form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['content_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Content Settings'),
      '#open' => TRUE,
    ];
    $form['content_settings']['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => [
        'page' => $this->t('Basic Page'),
        'enlaces_detacados' => $this->t('enlaces detacados'),
        'noticias' => $this->t('Noticias'),
      ],
      '#default_value' => $config['content_type'],
      '#description' => $this->t('Select the content type to display.'),
    ];
    $form['content_settings']['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of Items'),
      '#min' => 1,
      '#max' => 10,
      '#default_value' => $config['limit'] ?? $this->limit,
      '#description' => $this->t('Number of content items to display.'),
    ];
    $content_type = $config['content_type'];
    $limit = $config['limit'] ?? $this->limit;
    for ($i = 1; $i <= $limit; $i++) {
      $entity = NULL;
      if (isset($config['content_item_' . $i])) {
        $entity = $this->entityTypeManager->getStorage('node')->load($config['content_item_' . $i]);
      }
      $form['content_settings']['content_item_' . $i] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#title' => $this->t('Content Item @number', ['@number' => $i]),
        '#selection_handler' => 'default:node',
        '#selection_settings' => [
          'target_bundles' => [$content_type],
        ],
        '#default_value' => $entity,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $content_type = $form_state->getValue(['content_settings', 'content_type']);
    $limit = $form_state->getValue(['content_settings', 'limit']);

    for ($i = 1; $i <= $limit; $i++) {
      $node_id = $form_state->getValue(['content_settings', 'content_item_' . $i]);
      if ($node_id) {
        $node = $this->entityTypeManager->getStorage('node')->load($node_id);
        if ($node && $node->getType() !== $content_type) {
          $form_state->setErrorByName(
            'content_settings][content_item_' . $i,
            $this->t('The selected content does not match the chosen content type.')
          );
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->setConfigurationValue('content_type', $values['content_settings']['content_type']);
    $this->setConfigurationValue('block_title', $values['content_settings']['block_title']);
    $this->setConfigurationValue('limit', $values['content_settings']['limit']);
    $limit = $values['content_settings']['limit'];
    for ($i = 1; $i <= $limit; $i++) {
      $entity = $form_state->getValue(['content_settings', 'content_item_' . $i]);
      $this->setConfigurationValue('content_item_' . $i, $entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();


    $limit = $config['limit'] ?? $this->limit;
    $content_type = $config['content_type'] ?? 'article';

    $content = [];
    $is_home = $this->pathMatcher->isFrontPage();

    for ($i = 1; $i <= $limit; $i++) {
      $node_id = $config['content_item_' . $i] ?? NULL;
      if (!$node_id) {
        continue;
      }

      $node = $this->entityTypeManager->getStorage('node')->load($node_id);
      if (!$node || !$node->isPublished()) {
        continue;
      }

      $content[] = $this->buildContentItem($node, $content_type);
    }

    return [
      '#theme' => 'cnu_content_prominent_block',
      '#content' => $content,
      '#attached' => [
        'library' => [
          'cnu_content_blocks/cnu_content_blocks',
        ],
      ],
    ];
  }

  /**
   * Builds content item data.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node entity.
   * @param string $content_type
   *   The content type.
   *
   * @return array
   *   The content item data.
   */
  private function buildContentItem(Node $node, $content_type): array {
    $item = [
      'nid' => $node->id(),
      'title' => $node->getTitle(),
      'url' => $node->toUrl()->toString(),
      'created' => $this->dateFormatter->format($node->getCreatedTime(), 'custom', 'j F Y'),
      'created_timestamp' => $node->getCreatedTime(),
      'summary' => '',
      'image_url' => '',
    ];

    // Obtener resumen del contenido
    if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
      $body = $node->get('body')->first();
      $item['summary'] = $body->summary ?: text_summary($body->value, NULL, 200);
    }

    // Obtener imagen principal - versión simplificada para evitar errores
    $item['image_url'] = '';
    $image_fields = ['field_image', 'field_media_image', 'field_banner'];
    foreach ($image_fields as $field_name) {
      if ($node->hasField($field_name) && !$node->get($field_name)->isEmpty()) {
        try {
          $field_value = $node->get($field_name)->first();
          if ($field_value && isset($field_value->entity)) {
            $file = $field_value->entity;
            if ($file && $file->hasField('uri')) {
              $item['image_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->get('uri')->value);
              break;
            }
          }
          // Para campos de referencia de medios
          $image_entity = $field_value->entity ?? NULL;
          if ($image_entity && $image_entity->hasField('field_media_image')) {
            $media_image = $image_entity->get('field_media_image')->entity;
            if ($media_image && $media_image->hasField('uri')) {
              $item['image_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($media_image->get('uri')->value);
              break;
            }
          }
        } catch (\Exception $e) {
          // Ignorar errores de imagen y continuar
          continue;
        }
      }
    }

    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'content_type' => 'article',
      'block_title' => '',
      'limit' => $this->limit,
    ] + parent::defaultConfiguration();
  }

}
