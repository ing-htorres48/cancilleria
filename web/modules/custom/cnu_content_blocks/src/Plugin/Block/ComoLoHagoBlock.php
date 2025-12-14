<?php

namespace Drupal\cnu_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * @Block(
 *   id = "como_lo_hago_block",
 *   admin_label = @Translation("Cómo lo Hago - Videos"),
 * )
 */
class ComoLoHagoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $items = [];

    // Obtener nodos tipo como_lo_hago publicados.
    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'como_lo_hago')
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);

      foreach ($nodes as $node) {

        // Obtener el link embed directo.
        $video_url = $node->get('field_link_embed_youtube')->value ?? '';

        $items[] = [
          'title' => $node->label(),
          'url'   => $video_url,
        ];
      }
    }

    return [
      '#theme' => 'como_lo_hago_videos',
      '#items' => $items,
      '#cache' => ['max-age' => 0],
    ];
  }
}
