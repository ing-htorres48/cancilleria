
<?php

declare(strict_types=1);

namespace Drupal\menu_breadcrumb_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\menu_breadcrumb_custom\MenuBreadcrumbBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "menu_breadcrumb_custom_block",
 *   admin_label = @Translation("Menu Breadcrumb Custom"),
 *   category = @Translation("Navigation")
 * )
 */
final class MenuBreadcrumbBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, private readonly MenuBreadcrumbBuilder $builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu_breadcrumb_custom.builder'),
    );
  }

  public function build(): array {
    $path = \Drupal::service('path.current')->getPath();
    if (str_starts_with($path, '/admin')) {
      return [];
    }
    return $this->builder->buildRenderArray();
  }

}
