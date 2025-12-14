<?php

namespace Drupal\cnu_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * @Block(
 *   id = "cnu_footer_block",
 *   admin_label = @Translation("CNU Footer Block"),
 *   category = @Translation("CNU Blocks")
 * )
 */
class CnuFooterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'menu_machine_name' => 'main',
      'logo_gobierno' => NULL,
      'icontec1' => NULL,
      'icontec2' => NULL,
      'iqnet' => NULL,
      'email' => '',
      'link_x' => '',
      'facebook' => '',
      'youtube' => '',
      'instagram' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['menu_machine_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Menú a renderizar (machine name)'),
      '#default_value' => $this->configuration['menu_machine_name'],
    ];

    // ------ LOGOS (UPLOADS) -------
    $form['logo_gobierno'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Logo Gobierno'),
      '#upload_location' => 'public://cnu_footer/',
      '#default_value' => $this->configuration['logo_gobierno'],
    ];

    $form['icontec1'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Icontec 1'),
      '#upload_location' => 'public://cnu_footer/',
      '#default_value' => $this->configuration['icontec1'],
    ];

    $form['icontec2'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Icontec 2'),
      '#upload_location' => 'public://cnu_footer/',
      '#default_value' => $this->configuration['icontec2'],
    ];

    $form['iqnet'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('IQNet'),
      '#upload_location' => 'public://cnu_footer/',
      '#default_value' => $this->configuration['iqnet'],
    ];

    // ------ CAMPOS DE TEXTO -------
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Correo electrónico'),
      '#default_value' => $this->configuration['email'],
    ];

    $form['link_x'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Perfil X'),
      '#default_value' => $this->configuration['link_x'],
    ];

    $form['facebook'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook'),
      '#default_value' => $this->configuration['facebook'],
    ];

    $form['youtube'] = [
      '#type' => 'textfield',
      '#title' => $this->t('YouTube'),
      '#default_value' => $this->configuration['youtube'],
    ];

    $form['instagram'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Instagram'),
      '#default_value' => $this->configuration['instagram'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    foreach (['logo_gobierno','icontec1','icontec2','iqnet'] as $field) {
      $fid = $form_state->getValue($field);
      if ($fid && is_array($fid)) {
        $file = File::load($fid[0]);
        if ($file) {
          $file->setPermanent();
          $file->save();
        }
      }
      $this->configuration[$field] = $fid;
    }

    foreach (['menu_machine_name','email','link_x','facebook','youtube','instagram'] as $field) {
      $this->configuration[$field] = $form_state->getValue($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $menu_tree = \Drupal::menuTree();
    $menu_name = $this->configuration['menu_machine_name'];

    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $tree = $menu_tree->load($menu_name, $parameters);

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    $menu = $menu_tree->transform($tree, $manipulators);

    //var_dump(array_keys($menu));


    return [
      '#theme' => 'cnu_footer_block',
      '#menu' => $menu,
      '#config' => $this->configuration,
    ];
  }

}
