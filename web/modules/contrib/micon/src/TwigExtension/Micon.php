<?php

namespace Drupal\micon\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * A class providing Micon Twig extensions.
 *
 * This provides a Twig extension that registers the {{ micon() }} extension
 * to Twig.
 */
class Micon extends AbstractExtension {

  /**
   * Gets a unique identifier for this Twig extension.
   *
   * @return string
   *   A unique identifier for this Twig extension.
   */
  public function getName() {
    return 'twig.micon';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('micon', [$this, 'renderIcon']),
    ];
  }

  /**
   * Render the icon.
   *
   * @param string $icon
   *   The icon_id of the icon to render.
   *
   * @return mixed[]
   *   A render array.
   */
  public static function renderIcon($icon) {
    $build = [
      '#theme' => 'micon_icon',
      '#icon' => $icon,
    ];
    return $build;
  }

}
