<?php

namespace Drupal\cnu_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * @Block(
 *   id = "menu_rapido_contextual_block",
 *   admin_label = @Translation("Menú rápido contextual"),
 *   category = @Translation("CNU")
 * )
 */
class MenuRapidoContextualBlock extends BlockBase {

	protected function getTopLevelParent(string $menu_name, array $active_trail) {
		$menu_link_manager = \Drupal::service('plugin.manager.menu.link');

		foreach ($active_trail as $plugin_id) {
			// PROTECCIÓN CRÍTICA
			if (empty($plugin_id)) {
				continue;
			}

			if (!$menu_link_manager->hasDefinition($plugin_id)) {
				continue;
			}

			$link = $menu_link_manager->createInstance($plugin_id);

			// Nivel 1: no tiene padre o su padre es el menú
			if ($link->getParent() === '' || str_starts_with($link->getParent(), $menu_name . ':')) {
				return $plugin_id;
			}
		}

		return NULL;
	}


	public function build() {
		$menu_name = 'main';

		$active_trail = \Drupal::service('menu.active_trail')->getActiveTrailIds($menu_name);
		$top_level_parent = $this->getTopLevelParent($menu_name, $active_trail);

		if (
				empty($top_level_parent)||
				!is_string($top_level_parent)||
				!\Drupal::service('plugin.manager.menu.link')->hasDefinition($top_level_parent)
			) 
		{
			return [];
		}

		$menu_link_manager = \Drupal::service('plugin.manager.menu.link');

		// Cargar SOLO hijos directos del padre
		$parameters = new MenuTreeParameters();
		$parameters
			->setRoot($top_level_parent)
			->excludeRoot()
			->onlyEnabledLinks();

		$menu_tree = \Drupal::menuTree();
		$tree = $menu_tree->load($menu_name, $parameters);

		if (empty($tree)) {
			return [];
		}

		// Aplicar manipuladores estándar (orden, acceso, etc.)
		$tree = $menu_tree->transform($tree, [
			['callable' => 'menu.default_tree_manipulators:checkAccess'],
			['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
		]);

		$items = [];
		foreach ($tree as $element) {
			$plugin_id = $element->link->getPluginId();
			$items[] = [
				'title' => $element->link->getTitle(),
				'url' => $element->link->getUrlObject()->toString(),
				'active' => in_array($plugin_id, $active_trail, TRUE),
			];
		}

		// Obtener título del padre
		$parent_title = '';

		if (!empty($top_level_parent) && $menu_link_manager->hasDefinition($top_level_parent)) {
			$parent_title = $menu_link_manager
				->createInstance($top_level_parent)
				->getTitle();
		}

		return [
			'#theme' => 'menu_rapido_contextual',
			'#menu_title' => $parent_title,
			'#tree' => $tree,
			'#cache' => [
			'contexts' => [
				'url.path',
				'route',
			],
			],
		];
	}

}
