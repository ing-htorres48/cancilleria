<?php

namespace Drupal\cnu_content_blocks\Plugin\Block;

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

		$menu_tree = \Drupal::menuTree();
		$menu_link_manager = \Drupal::service('plugin.manager.menu.link');
		$active_trail = \Drupal::service('menu.active_trail')->getActiveTrailIds($menu_name);

		$top_level_parent = $this->getTopLevelParent($menu_name, $active_trail);

		$parameters = new MenuTreeParameters();
		$parameters->onlyEnabledLinks();

		// Caso 1: hay contexto → menú contextual
		if (
			!empty($top_level_parent) &&
			is_string($top_level_parent) &&
			$menu_link_manager->hasDefinition($top_level_parent)
		) {
			$parameters
			->setRoot($top_level_parent)
			->excludeRoot();
		}
		// Caso 2: no hay contexto → fallback (mostrar menú completo)
		else {
			$parameters->setMaxDepth(3);
		}

		$tree = $menu_tree->load($menu_name, $parameters);

		if (!$tree) {
			return [];
		}

		$tree = $menu_tree->transform($tree, [
			['callable' => 'menu.default_tree_manipulators:checkAccess'],
			['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
		]);

		// Título del bloque (padre si existe)
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
				'max-age' => 0,
			],
		];
	}


}
