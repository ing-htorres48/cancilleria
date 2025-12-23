<?php

namespace Drupal\cancilleria_menu_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Provides a dynamic menu block by machine name.
 *
 * @Block(
 *   id = "cancilleria_dynamic_menu_block",
 *   admin_label = @Translation("Menú dinámico (por nombre de sistema)"),
 *   category = @Translation("Cancillería")
 * )
 */
class DynamicMenuBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'menu_machine_name' => '',
      'menu_depth' => 3,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['menu_machine_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre de sistema del menú'),
      '#description' => $this->t('Ejemplo: main, footer, menu-interno.'),
      '#default_value' => $this->configuration['menu_machine_name'],
      '#required' => TRUE,
    ];

    $form['menu_depth'] = [
      '#type' => 'number',
      '#title' => $this->t('Profundidad máxima'),
      '#default_value' => $this->configuration['menu_depth'],
      '#min' => 1,
      '#max' => 10,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['menu_machine_name'] = $form_state->getValue('menu_machine_name');
    $this->configuration['menu_depth'] = (int) $form_state->getValue('menu_depth');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $menu_name = $this->configuration['menu_machine_name'];

    if (empty($menu_name)) {
      return [];
    }

    $menu_tree = \Drupal::menuTree();

    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth($this->configuration['menu_depth']);

    $tree = $menu_tree->load($menu_name, $parameters);

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    $build = $menu_tree->build($tree);
    $build['#cache']['contexts'][] = 'url.path';
    $build['#cache']['contexts'][] = 'user.roles';
    $build['#cache']['tags'][] = "config:system.menu.$menu_name";

    return $build;
  }

}
