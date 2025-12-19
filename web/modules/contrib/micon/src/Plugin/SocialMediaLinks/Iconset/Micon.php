<?php

namespace Drupal\micon\Plugin\SocialMediaLinks\Iconset;

use Drupal\social_media_links\IconsetBase;
use Drupal\social_media_links\IconsetInterface;

/**
 * Provides 'Micon' iconset.
 *
 * @Iconset(
 *   id = "micon",
 *   publisher = "JaceRider",
 *   publisherUrl = "https://github.com/jacerider/micon",
 *   name = "Micon",
 * )
 */
class Micon extends IconsetBase implements IconsetInterface {

  /**
   * {@inheritdoc}
   */
  public function setPath($iconset_id) {
    $this->path = 'library';
  }

  /**
   * {@inheritdoc}
   */
  public function getStyle() {
    return [
      'normal' => 'normal',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIconElement($platform, $style) {
    $icon_name = $platform->getIconName();

    switch ($icon_name) {
      case 'vimeo':
        $icon_name = $icon_name . '-square';
        break;

      case 'googleplus':
        $icon_name = 'google-plus';
        break;

      case 'email':
        $icon_name = 'envelope';
        break;

      case 'rss':
        $icon_name = 'feed';
        break;

      case 'website':
        $icon_name = 'home';
        break;
    }

    $icon = [
      '#theme' => 'micon',
      '#icon' => 'fa-' . $icon_name,
    ];

    return $icon;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconPath($icon_name, $style) {
    return NULL;
  }

}
