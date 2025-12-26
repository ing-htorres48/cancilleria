/**
 * @file
 * JavaScript principal para el tema Colombianos UNE D11
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Comportamiento principal del tema
   */
  Drupal.behaviors.colombianosUneTheme = {
    attach: function (context, settings) {

      // Inicializar componentes una vez
      $(document, context).once('colombianos-une-init').each(function () {

        // Mejorar navegación móvil
        initMobileNavigation();

        // Inicializar formularios
        enhanceFormElements();

        // Agregar efectos de scroll
        initScrollEffects();

        // Inicializar tooltips de Bootstrap
        if (typeof bootstrap !== 'undefined') {
          var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
          });
        }

        // Inicializar popovers de Bootstrap
        if (typeof bootstrap !== 'undefined') {
          var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
          popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
          });
        }

        // Smooth scroll para enlaces internos
        initSmoothScroll();

        // Lazy loading para imágenes
        initLazyLoading();

        // Analytics y tracking
        initAnalytics();
      });
    }
  };

  /**
   * Mejorar navegación móvil
   */
  function initMobileNavigation() {
    var $navbar = $('.navbar');
    var $navbarToggler = $('.navbar-toggler');
    var $navbarCollapse = $('.navbar-collapse');

    // Cerrar menú al hacer clic fuera
    $(document).on('click', function (e) {
      if (!$navbar.is(e.target) && $navbar.has(e.target).length === 0) {
        if ($navbarCollapse.hasClass('show')) {
          $navbarToggler.click();
        }
      }
    });

    // Cerrar menú al hacer clic en un enlace
    $('.navbar-nav .nav-link').on('click', function () {
      if ($navbarCollapse.hasClass('show')) {
        $navbarToggler.click();
      }
    });

    // Agregar clase para scroll
    $(window).on('scroll', function () {
      if ($(window).scrollTop() > 100) {
        $navbar.addClass('navbar-scrolled');
      } else {
        $navbar.removeClass('navbar-scrolled');
      }
    });
  }

  /**
   * Mejorar elementos de formulario
   */
  function enhanceFormElements() {
    // Agregar clases Bootstrap a formularios
    $('.form-item input[type="text"], .form-item input[type="email"], .form-item input[type="password"], .form-item textarea, .form-item select')
      .addClass('form-control');

    $('.form-item input[type="checkbox"]').addClass('form-check-input');
    $('.form-item input[type="radio"]').addClass('form-check-input');

    // Agregar labels flotantes
    $('.form-control').each(function () {
      var $input = $(this);
      var $wrapper = $input.closest('.form-item');

      if ($wrapper.find('label').length > 0) {
        $wrapper.addClass('form-floating');
      }
    });

    // Validación en tiempo real
    $('.form-control').on('blur', function () {
      validateField($(this));
    });

    // Envío de formularios con loading
    $('form').on('submit', function () {
      var $form = $(this);
      var $submitBtn = $form.find('input[type="submit"], button[type="submit"]');

      $submitBtn.addClass('loading').prop('disabled', true);

      // Agregar spinner si no existe
      if (!$submitBtn.find('.spinner-border').length) {
        $submitBtn.prepend('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
      }
    });
  }

  /**
   * Validar campo individual
   */
  function validateField($field) {
    var value = $field.val().trim();
    var isValid = true;
    var errorMessage = '';

    // Validaciones básicas
    if ($field.prop('required') && !value) {
      isValid = false;
      errorMessage = 'Este campo es requerido';
    } else if ($field.attr('type') === 'email' && value) {
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        errorMessage = 'Formato de email inválido';
      }
    }

    // Aplicar estilos de validación
    if (isValid) {
      $field.removeClass('is-invalid').addClass('is-valid');
      $field.siblings('.invalid-feedback').remove();
    } else {
      $field.removeClass('is-valid').addClass('is-invalid');

      if (!$field.siblings('.invalid-feedback').length) {
        $field.after('<div class="invalid-feedback">' + errorMessage + '</div>');
      }
    }
  }

  /**
   * Efectos de scroll
   */
  function initScrollEffects() {
    // Botón "volver arriba"
    var $backToTop = $('<button id="back-to-top" class="btn btn-primary btn-floating" title="Volver arriba"><i class="bi bi-arrow-up"></i></button>');
    $('body').append($backToTop);

    $(window).on('scroll', function () {
      if ($(window).scrollTop() > 300) {
        $backToTop.fadeIn();
      } else {
        $backToTop.fadeOut();
      }
    });

    $backToTop.on('click', function () {
      $('html, body').animate({ scrollTop: 0 }, 600);
    });

    // Animaciones de entrada para elementos
    if (typeof IntersectionObserver !== 'undefined') {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
          }
        });
      });

      document.querySelectorAll('.node, .block, .view').forEach(function (el) {
        observer.observe(el);
      });
    }
  }

  /**
   * Smooth scroll para enlaces internos
   */
  function initSmoothScroll() {
    $('a[href^="#"]').on('click', function (e) {
      var target = $(this.getAttribute('href'));

      if (target.length) {
        e.preventDefault();
        $('html, body').animate({
          scrollTop: target.offset().top - 100
        }, 600);
      }
    });
  }

  /**
   * Lazy loading para imágenes
   */
  function initLazyLoading() {
    if (typeof IntersectionObserver !== 'undefined') {
      var imageObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            img.classList.add('loaded');
            imageObserver.unobserve(img);
          }
        });
      });

      document.querySelectorAll('img[data-src]').forEach(function (img) {
        imageObserver.observe(img);
      });
    }
  }

  /**
   * Configurar analytics básico
   */
  function initAnalytics() {
    // Track clicks en enlaces externos
    $('a[href^="http"]:not([href*="' + location.hostname + '"])').on('click', function () {
      var url = $(this).attr('href');
      if (typeof gtag !== 'undefined') {
        gtag('event', 'click', {
          event_category: 'external_link',
          event_label: url
        });
      }
    });

    // Track descargas
    $('a[href$=".pdf"], a[href$=".doc"], a[href$=".docx"], a[href$=".xls"], a[href$=".xlsx"], a[href$=".zip"]').on('click', function () {
      var url = $(this).attr('href');
      if (typeof gtag !== 'undefined') {
        gtag('event', 'download', {
          event_category: 'file_download',
          event_label: url
        });
      }
    });
  }

  /**
   * Utilidad para mostrar notificaciones
   */
  Drupal.theme.showNotification = function (message, type) {
    type = type || 'info';
    var alertClass = 'alert-' + type;

    var $notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show notification-custom" role="alert">')
      .html(message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');

    $('.main-content').prepend($notification);

    // Auto-hide después de 5 segundos
    setTimeout(function () {
      $notification.alert('close');
    }, 5000);
  };

})(jQuery, Drupal);
