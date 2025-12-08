/**
 * @file
 * JavaScript para componentes de navegación
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Comportamiento para navegación avanzada
   */
  Drupal.behaviors.navigationEnhanced = {
    attach: function (context, settings) {

      // Mega menú
      $('.navbar-nav .dropdown', context).once('mega-menu').each(function () {
        var $dropdown = $(this);
        var $toggle = $dropdown.find('.dropdown-toggle');
        var $menu = $dropdown.find('.dropdown-menu');

        // Hover para desktop
        if (window.innerWidth > 991) {
          $dropdown.on('mouseenter', function () {
            $toggle.dropdown('show');
          });

          $dropdown.on('mouseleave', function () {
            $toggle.dropdown('hide');
          });
        }

        // Prevenir cierre al hacer clic dentro del menú
        $menu.on('click', function (e) {
          e.stopPropagation();
        });
      });

      // Navegación sticky
      var $navbar = $('.navbar', context);
      if ($navbar.length) {
        $(window).once('sticky-nav').on('scroll', function () {
          var scrollTop = $(window).scrollTop();

          if (scrollTop > 100) {
            $navbar.addClass('navbar-scrolled');
          } else {
            $navbar.removeClass('navbar-scrolled');
          }
        });
      }

      // Indicador de página activa
      $('.navbar-nav .nav-link', context).once('active-indicator').each(function () {
        var $link = $(this);
        var href = $link.attr('href');
        var currentPath = window.location.pathname;

        if (href === currentPath || (href !== '#' && currentPath.includes(href))) {
          $link.addClass('active');
          $link.closest('.dropdown').find('.dropdown-toggle').addClass('active');
        }
      });

      // Breadcrumb dinámico
      generateDynamicBreadcrumb();

      // Mobile menu toggle
      $('.sidebar-menu-toggle', context).once('mobile-menu').on('click', function () {
        $(this).closest('.sidebar-menu').toggleClass('menu-open');
      });

      // Search in navbar
      $('.navbar-search .form-control', context).once('navbar-search').on('focus blur', function (e) {
        var $searchContainer = $(this).closest('.navbar-search');

        if (e.type === 'focus') {
          $searchContainer.addClass('search-focused');
        } else {
          $searchContainer.removeClass('search-focused');
        }
      });

      // Keyboard navigation
      $('.navbar-nav', context).once('keyboard-nav').on('keydown', '.nav-link', function (e) {
        var $current = $(this);
        var $links = $('.navbar-nav .nav-link');
        var currentIndex = $links.index($current);

        switch (e.keyCode) {
          case 37: // Left arrow
            e.preventDefault();
            if (currentIndex > 0) {
              $links.eq(currentIndex - 1).focus();
            }
            break;
          case 39: // Right arrow
            e.preventDefault();
            if (currentIndex < $links.length - 1) {
              $links.eq(currentIndex + 1).focus();
            }
            break;
        }
      });
    }
  };

  /**
   * Generar breadcrumb dinámico
   */
  function generateDynamicBreadcrumb() {
    var $breadcrumb = $('.breadcrumb');
    if ($breadcrumb.length && $breadcrumb.children().length <= 1) {
      var pathArray = window.location.pathname.split('/').filter(Boolean);
      var $breadcrumbList = $('<ol class="breadcrumb"></ol>');

      // Agregar inicio
      $breadcrumbList.append('<li class="breadcrumb-item"><a href="/"><i class="bi bi-house"></i> Inicio</a></li>');

      var currentPath = '';
      pathArray.forEach(function (segment, index) {
        currentPath += '/' + segment;
        var isLast = index === pathArray.length - 1;
        var segmentTitle = segment.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

        if (isLast) {
          $breadcrumbList.append('<li class="breadcrumb-item active">' + segmentTitle + '</li>');
        } else {
          $breadcrumbList.append('<li class="breadcrumb-item"><a href="' + currentPath + '">' + segmentTitle + '</a></li>');
        }
      });

      $breadcrumb.replaceWith($breadcrumbList);
    }
  }

  /**
   * Comportamiento para tabs de navegación
   */
  Drupal.behaviors.navigationTabs = {
    attach: function (context, settings) {
      $('.nav-tabs .nav-link', context).once('tab-navigation').on('click', function (e) {
        e.preventDefault();

        var $tab = $(this);
        var targetId = $tab.attr('href');
        var $tabContent = $(targetId);

        if ($tabContent.length) {
          // Activar tab
          $tab.closest('.nav-tabs').find('.nav-link').removeClass('active');
          $tab.addClass('active');

          // Mostrar contenido
          $tabContent.siblings('.tab-pane').removeClass('active show');
          $tabContent.addClass('active show');

          // Trigger evento personalizado
          $(document).trigger('tabChanged', [$tab, $tabContent]);
        }
      });

      // Navegación con teclado en tabs
      $('.nav-tabs', context).once('tab-keyboard').on('keydown', '.nav-link', function (e) {
        var $current = $(this);
        var $tabs = $current.closest('.nav-tabs').find('.nav-link');
        var currentIndex = $tabs.index($current);

        switch (e.keyCode) {
          case 37: // Left arrow
            e.preventDefault();
            if (currentIndex > 0) {
              $tabs.eq(currentIndex - 1).click().focus();
            }
            break;
          case 39: // Right arrow
            e.preventDefault();
            if (currentIndex < $tabs.length - 1) {
              $tabs.eq(currentIndex + 1).click().focus();
            }
            break;
        }
      });
    }
  };

  /**
   * Back to top button
   */
  Drupal.behaviors.backToTop = {
    attach: function (context, settings) {
      $(document, context).once('back-to-top').each(function () {
        // Crear botón si no existe
        if (!$('#back-to-top').length) {
          var $backToTop = $('<button id="back-to-top" class="btn btn-primary btn-floating" title="Volver arriba" aria-label="Volver arriba"><i class="bi bi-arrow-up"></i></button>');
          $('body').append($backToTop);

          // Posicionamiento con CSS
          $backToTop.css({
            position: 'fixed',
            bottom: '2rem',
            right: '2rem',
            width: '3rem',
            height: '3rem',
            borderRadius: '50%',
            zIndex: 1000,
            display: 'none'
          });
        }

        var $backToTop = $('#back-to-top');

        // Mostrar/ocultar en scroll
        $(window).on('scroll', function () {
          if ($(window).scrollTop() > 300) {
            $backToTop.fadeIn();
          } else {
            $backToTop.fadeOut();
          }
        });

        // Smooth scroll al top
        $backToTop.on('click', function () {
          $('html, body').animate({
            scrollTop: 0
          }, {
            duration: 600,
            easing: 'swing'
          });
        });
      });
    }
  };

  /**
   * Paginación Ajax
   */
  Drupal.behaviors.ajaxPagination = {
    attach: function (context, settings) {
      $('.pager .pager__link', context).once('ajax-pagination').on('click', function (e) {
        e.preventDefault();

        var $link = $(this);
        var url = $link.attr('href');
        var $container = $link.closest('[data-ajax-container]');

        if ($container.length) {
          // Mostrar loading
          $container.addClass('loading');

          // Hacer petición Ajax
          $.get(url)
            .done(function (data) {
              var $newContent = $(data).find('[data-ajax-container]').html();
              $container.html($newContent);

              // Trigger evento
              $(document).trigger('contentUpdated', [$container]);
            })
            .fail(function () {
              Drupal.theme.showNotification('Error al cargar el contenido', 'danger');
            })
            .always(function () {
              $container.removeClass('loading');
            });
        }
      });
    }
  };

})(jQuery, Drupal);
