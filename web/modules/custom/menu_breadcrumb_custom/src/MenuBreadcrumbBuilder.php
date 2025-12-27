
<?php

declare(strict_types=1);

namespace Drupal\menu_breadcrumb_custom;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds breadcrumbs from a configured menu (Drupal 11 compatible).
 */
final class MenuBreadcrumbBuilder {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly MenuLinkTreeInterface $menuLinkTree,
    private readonly CurrentPathStack $currentPath,
    private readonly AliasManagerInterface $aliasManager,
    private readonly CurrentRouteMatch $routeMatch,
    private readonly RequestStack $requestStack,
    private readonly TitleResolverInterface $titleResolver,
  ) {}

  public function buildRenderArray(): array {
    $config = $this->configFactory->get('menu_breadcrumb_custom.settings');
    $menu_name = (string) ($config->get('menu_name') ?: 'main');
    $show_home = (bool) $config->get('show_home');
    $home_label = (string) ($config->get('home_label') ?: 'Inicio');
    $always_show_current = (bool) $config->get('always_show_current_page');

    $current_internal = $this->normalizePath($this->currentPath->getPath()); // e.g. /node/123
    $current_alias = $this->normalizePath($this->aliasManager->getAliasByPath($current_internal)); // e.g. /tramites/...

    $current_node_id = $this->getCurrentNodeId();

    $trail = $this->findTrailInMenu($menu_name, $current_internal, $current_alias, $current_node_id);

    $items = [];
    if ($show_home) {
      $items[] = [
        'title' => $home_label,
        'url' => Url::fromRoute('<front>'),
        'link' => TRUE,
      ];
    }

    if (!empty($trail)) {
      foreach ($trail as $t) {
        $items[] = $t;
      }
      // Ensure last is current (no-link).
      $items[count($items) - 1]['link'] = FALSE;
      $items[count($items) - 1]['url'] = NULL;
    }
    elseif ($always_show_current) {
      $items[] = [
        'title' => $this->getCurrentPageTitle(),
        'url' => NULL,
        'link' => FALSE,
      ];
    }

    // If we only have "Inicio", don't render anything.
    if (count($items) < 2) {
      return [];
    }

    return $this->render($items, $menu_name);
  }

  private function findTrailInMenu(string $menu_name, string $current_internal, string $current_alias, ?int $current_node_id): array {
    $params = (new MenuTreeParameters())->onlyEnabledLinks();
    $tree = $this->menuLinkTree->load($menu_name, $params);

    $found = $this->walk($tree, [], $current_internal, $current_alias, $current_node_id);
    return $found ?? [];
  }

  /**
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   * @param array $trail
   */
  private function walk(array $tree, array $trail, string $current_internal, string $current_alias, ?int $current_node_id): ?array {
    foreach ($tree as $element) {
      if (!$element instanceof MenuLinkTreeElement) {
        continue;
      }

      $link = $element->link;
      $url = $link->getUrlObject();

      // Skip invalid/unrouted URLs.
      if (!$url instanceof Url) {
        continue;
      }

      $title = (string) $link->getTitle();

      // Determine if this menu link matches current page.
      if ($this->urlMatchesCurrent($url, $current_internal, $current_alias, $current_node_id)) {
        return array_merge($trail, [[
          'title' => $title,
          'url' => $url,
          'link' => TRUE,
        ]]);
      }

      if (!empty($element->subtree)) {
        $next_trail = array_merge($trail, [[
          'title' => $title,
          'url' => $url,
          'link' => TRUE,
        ]]);
        $found = $this->walk($element->subtree, $next_trail, $current_internal, $current_alias, $current_node_id);
        if ($found) {
          return $found;
        }
      }
    }
    return NULL;
  }

  private function urlMatchesCurrent(Url $url, string $current_internal, string $current_alias, ?int $current_node_id): bool {
    // 1) Exact match by route (node canonical).
    if ($current_node_id !== NULL && $url->isRouted()) {
      if ($url->getRouteName() === 'entity.node.canonical') {
        $params = $url->getRouteParameters();
        if (isset($params['node']) && (int) $params['node'] === $current_node_id) {
          return TRUE;
        }
      }
    }

    // 2) Match by internal path.
    $link_internal = $this->internalPathFromUrl($url); // "/node/123" or "/tramites"
    if ($link_internal && $link_internal === $current_internal) {
      return TRUE;
    }

    // 3) Match by alias of the link internal path (covers menu item pointing to /node/123 while page is alias).
    if ($link_internal) {
      $link_alias = $this->normalizePath($this->aliasManager->getAliasByPath($link_internal));
      if ($link_alias === $current_alias) {
        return TRUE;
      }
    }

    // 4) As last resort, compare string URL (may include base path).
    try {
      $as_string = $this->normalizePath($url->toString());
      if ($as_string === $current_alias || $as_string === $current_internal) {
        return TRUE;
      }
    }
    catch (\Throwable) {}

    return FALSE;
  }

  private function internalPathFromUrl(Url $url): ?string {
    try {
      $internal = $url->getInternalPath(); // "node/123" or "<front>" or "tramites"
      if ($internal === '<front>') {
        return '/';
      }
      // getInternalPath does not include leading slash.
      return $this->normalizePath('/' . ltrim($internal, '/'));
    }
    catch (\Throwable) {
      return NULL;
    }
  }

  private function normalizePath(string $path): string {
    $path = trim($path);
    if ($path === '') {
      return '/';
    }
    // Remove scheme/host if any.
    $path = preg_replace('#^https?://[^/]+#', '', $path);
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

  private function getCurrentNodeId(): ?int {
    $node = $this->routeMatch->getParameter('node');
    if (is_object($node) && method_exists($node, 'id')) {
      return (int) $node->id();
    }
    if (is_numeric($node)) {
      return (int) $node;
    }
    return NULL;
  }

  private function getCurrentPageTitle(): string {
    $request = $this->requestStack->getCurrentRequest();
    if (!$request) {
      return 'Página';
    }
    $title = $this->titleResolver->getTitle($request, $this->routeMatch->getRouteObject());
    return (is_string($title) && $title !== '') ? $title : 'Página';
  }

  private function render(array $items, string $menu_name): array {
    $list_items = [];
    foreach ($items as $idx => $item) {
      $is_last = ($idx === count($items) - 1);

      if (!$is_last && !empty($item['link']) && $item['url'] instanceof Url) {
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
        'tags' => ['config:system.menu.' . $menu_name],
      ],
    ];
  }

}
