<?php

namespace Drupal\redes_sociales_tabs\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Proporciona un bloque de pestañas de redes sociales.
 *
 * @Block(
 *   id = "social_tabs_block",
 *   admin_label = @Translation("Redes sociales (Facebook / Instagram / X)"),
 * )
 */
class SocialTabsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'social_tabs_block',
      '#attributes' => [
        'class' => ['social-tabs-wrapper'],
      ],
      '#content' => [],
      '#attached' => [
        'library' => [
          'redes_sociales_tabs/social_tabs',
        ],
      ],
    ];
  }

}
