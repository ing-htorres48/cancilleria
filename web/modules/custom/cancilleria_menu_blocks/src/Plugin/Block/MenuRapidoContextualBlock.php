<?php

namespace Drupal\cancilleria_menu_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Cache\Cache;

/**
 * @Block(
 *   id = "menu_rapido_contextual_block",
 *   admin_label = @Translation("Menú rápido contextual"),
 *   category = @Translation("CNU")
 * )
 */
class MenuRapidoContextualBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $menu_name = 'main';

    $menu_tree = \Drupal::menuTree();
    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $route_match = \Drupal::routeMatch();

    $route_name = $route_match->getRouteName();
    $route_parameters = $route_match->getRawParameters()->all();

    // Buscar ítems de menú que apunten a ESTA ruta
    $links = $menu_link_manager->loadLinksByRoute(
      $route_name,
      $route_parameters,
      $menu_name
    );

    if (empty($links)) {
      // Si la ruta no está directamente en el menú, no mostramos nada
      return [];
    }

    // Tomamos el primer match (normalmente único)
    $menu_link = reset($links);
    $plugin_id = $menu_link->getPluginId();

    // Parámetros del árbol
    $parameters = new MenuTreeParameters();
    $parameters
      ->onlyEnabledLinks()
      ->setRoot($plugin_id)
      ->excludeRoot();

    $tree = $menu_tree->load($menu_name, $parameters);

    // Si este ítem no tiene hijos, subimos al padre
    if (empty($tree) && $menu_link->getParent()) {
      $parameters
        ->setRoot($menu_link->getParent())
        ->excludeRoot();

      $tree = $menu_tree->load($menu_name, $parameters);
      $plugin_id = $menu_link->getParent();
    }

    if (empty($tree)) {
      return [];
    }

    $tree = $menu_tree->transform($tree, [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ]);

    $menu_title = $menu_link_manager
      ->createInstance($plugin_id)
      ->getTitle();

    return [
      '#theme' => 'menu_rapido_contextual',
      '#menu_title' => $menu_title,
      '#tree' => $tree,
      '#cache' => [
        'contexts' => Cache::mergeContexts(
          ['route', 'url.path'],
          ['user.permissions']
        ),
      ],
    ];
  }

}
