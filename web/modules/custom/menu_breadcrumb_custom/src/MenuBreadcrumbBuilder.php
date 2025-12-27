<?php

declare(strict_types=1);

namespace Drupal\menu_breadcrumb_custom;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Url;

final class MenuBreadcrumbBuilder {

  public function __construct(
    private ConfigFactoryInterface $configFactory,
    private MenuLinkTreeInterface $menuLinkTree,
    private CurrentPathStack $currentPath,
    private AliasManagerInterface $aliasManager,
    private CurrentRouteMatch $routeMatch,
    private RequestStack $requestStack,
    private TitleResolverInterface $titleResolver,
  ) {}

  public function buildRenderArray(): array {
    $config = $this->configFactory->get('menu_breadcrumb_custom.settings');
    $menu = $config->get('menu_name') ?? 'main';

    $path = $this->currentPath->getPath();
    $alias = $this->aliasManager->getAliasByPath($path);

    $items = [
      [
        'title' => $config->get('home_label') ?? 'Inicio',
        'url' => Url::fromRoute('<front>'),
      ],
      [
        'title' => $this->getTitle(),
        'url' => NULL,
      ],
    ];

    return [
      '#markup' => '<nav class="breadcrumb"><ol><li><a href="/">Inicio</a></li><li aria-current="page">' . $this->getTitle() . '</li></ol></nav>',
      '#allowed_tags' => ['nav','ol','li','a'],
    ];
  }

  private function getTitle(): string {
    $request = $this->requestStack->getCurrentRequest();
    $title = $this->titleResolver->getTitle($request, $this->routeMatch->getRouteObject());
    return is_string($title) ? $title : 'Página';
  }
}
