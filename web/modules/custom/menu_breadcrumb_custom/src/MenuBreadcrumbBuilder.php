<?php

declare(strict_types=1);

namespace Drupal\menu_breadcrumb_custom;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\DefaultMenuLinkTreeManipulators;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class MenuBreadcrumbBuilder {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly MenuLinkTreeInterface $menuLinkTree,
    private readonly CurrentPathStack $currentPath,
    private readonly AliasManagerInterface $aliasManager,
    private readonly CurrentRouteMatch $currentRouteMatch,
    private readonly RequestStack $requestStack,
    private readonly TitleResolverInterface $titleResolver,
    private readonly AdminContext $adminContext,
  ) {}

  /**
   * Render array listo para imprimir en Twig.
   */
  public function buildRenderArray(): array {
    if ($this->adminContext->isAdminRoute()) {
      return [];
    }

    $config = $this->configFactory->get('menu_breadcrumb_custom.settings');
    $menu_name = (string) ($config->get('menu_name') ?: 'main');
    $show_home = (bool) $config->get('show_home');
    $home_label = (string) ($config->get('home_label') ?: 'Inicio');
    $always_show_current = (bool) $config->get('always_show_current_page');
    $hide_on_front = (bool) $config->get('hide_on_front');

    $internal_path = $this->normalizePath($this->currentPath->getPath());
    if ($hide_on_front && $internal_path === '/') {
      return [];
    }

    $alias_path = $this->normalizePath($this->aliasManager->getAliasByPath($internal_path));

    $trail = $this->findTrailInMenu($menu_name, $internal_path, $alias_path);

    $items = [];
    if ($show_home) {
      $items[] = [
        'title' => $home_label,
        'url' => Url::fromRoute('<front>'),
        'is_link' => TRUE,
      ];
    }

    if (!empty($trail)) {
      $items = array_merge($items, $trail);
    }
    elseif ($always_show_current) {
      $items[] = [
        'title' => $this->getCurrentPageTitle(),
        'url' => NULL,
        'is_link' => FALSE,
      ];
    }

    $items = $this->dedupe($items);

    if (count($items) < 2) {
      return [];
    }

    return $this->renderBreadcrumb($items);
  }

  private function normalizePath(string $path): string {
    $path = trim($path);
    if ($path === '') {
      return '/';
    }
    if ($path[0] !== '/') {
      $path = '/' . $path;
    }
    if ($path !== '/') {
      $path = rtrim($path, '/');
    }
    return $path;
  }

  /**
   * Carga el árbol y busca el trail comparando internal path y alias.
   */
  private function findTrailInMenu(string $menu_name, string $internal_path, string $alias_path): array {
    $parameters = (new MenuTreeParameters())
      ->onlyEnabledLinks();

    $tree = $this->menuLinkTree->load($menu_name, $parameters);

    // Respetar acceso y orden.
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuLinkTree->transform($tree, $manipulators);

    $found = $this->walkTree($tree, [], $internal_path, $alias_path);
    return $found ?? [];
  }

  private function walkTree(array $tree, array $trail, string $internal_path, string $alias_path): ?array {
    foreach ($tree as $element) {
      $link = $element->link;
      $url = $link->getUrlObject();

      // Ignorar enlaces externos.
      if ($url->isExternal()) {
        continue;
      }

      // internal path sin alias (p.ej. 'node/123' o '' para <front>).
      $candidate_internal = $url->getInternalPath();
      $candidate_internal = $candidate_internal === '' ? '/' : '/' . ltrim($candidate_internal, '/');
      $candidate_internal = $this->normalizePath($candidate_internal);

      // Alias del enlace, si existe.
      $candidate_alias = $this->normalizePath($this->aliasManager->getAliasByPath($candidate_internal));

      $current_item = [
        'title' => $link->getTitle(),
        'url' => $url,
        'is_link' => TRUE,
      ];
      $new_trail = array_merge($trail, [$current_item]);

      if ($candidate_internal === $internal_path || $candidate_alias === $alias_path) {
        // Último elemento no clickeable.
        $new_trail[count($new_trail) - 1]['is_link'] = FALSE;
        $new_trail[count($new_trail) - 1]['url'] = NULL;
        return $new_trail;
      }

      if (!empty($element->subtree)) {
        $found = $this->walkTree($element->subtree, $new_trail, $internal_path, $alias_path);
        if ($found) {
          return $found;
        }
      }
    }
    return NULL;
  }

  private function getCurrentPageTitle(): string {
    $request = $this->requestStack->getCurrentRequest();
    if (!$request) {
      return 'Página';
    }
    $route = $this->currentRouteMatch->getRouteObject();
    if ($route) {
      $title = $this->titleResolver->getTitle($request, $route);
      if (is_string($title) && $title !== '') {
        return $title;
      }
    }
    return 'Página';
  }

  private function dedupe(array $items): array {
    $out = [];
    $seen_home = FALSE;

    foreach ($items as $item) {
      $title = trim((string) ($item['title'] ?? ''));
      if ($title === '') {
        continue;
      }
      $key = mb_strtolower($title);

      if ($key === 'inicio') {
        if ($seen_home) {
          continue;
        }
        $seen_home = TRUE;
      }

      // Evita duplicados consecutivos.
      if (!empty($out)) {
        $prev = mb_strtolower(trim((string) $out[count($out) - 1]['title']));
        if ($prev === $key) {
          continue;
        }
      }

      $out[] = $item;
    }

    return $out;
  }

  private function renderBreadcrumb(array $items): array {
    $list_items = [];
    foreach ($items as $idx => $item) {
      $is_last = ($idx === count($items) - 1);

      if (!$is_last && !empty($item['is_link']) && ($item['url'] instanceof Url)) {
        $list_items[] = Link::fromTextAndUrl((string) $item['title'], $item['url'])->toRenderable();
      }
      else {
        $list_items[] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => (string) $item['title'],
          '#attributes' => $is_last ? ['aria-current' => 'page'] : [],
        ];
      }
    }

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['menu-breadcrumb-custom']],
      'nav' => [
        '#type' => 'html_tag',
        '#tag' => 'nav',
        '#attributes' => [
          'class' => ['breadcrumb'],
          'aria-label' => 'Breadcrumb',
        ],
        'list' => [
          '#theme' => 'item_list',
          '#items' => $list_items,
          '#attributes' => ['class' => ['breadcrumb__list']],
        ],
      ],
      '#cache' => [
        'contexts' => ['url.path', 'user.permissions'],
        'tags' => ['config:menu_breadcrumb_custom.settings', 'config:system.menu.' . $this->configFactory->get('menu_breadcrumb_custom.settings')->get('menu_name')],
      ],
    ];
  }

}
