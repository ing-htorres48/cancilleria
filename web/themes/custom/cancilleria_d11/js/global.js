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

      // Inicializar componentes UNA SOLA VEZ
      $(document, context).once('colombianos-une-init').each(function () {

        initMobileNavigation();
        enhanceFormElements();
        initScrollEffects();
        initStickyHeader();
        initSmoothScroll();
        initLazyLoading();
        initAnalytics();

        // Tooltips Bootstrap
        if (typeof bootstrap !== 'undefined') {
          var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
          });
        }

        // Popovers Bootstrap
        if (typeof bootstrap !== 'undefined') {
          var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
          popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
          });
        }
      });
    }
  };

  /**
   * Header fijo al hacer scroll (GOV.CO)
   */
  function initStickyHeader() {
    const nav = document.querySelector('.govco-header__nav');
    const middle = document.querySelector('.govco-header__middle');

    if (!nav || !middle) {
      return;
    }

    window.addEventListener('scroll', function () {
      const middleBottom = middle.offsetTop + middle.offsetHeight;

      if (window.scrollY >= middleBottom - 50) {
        nav.classList.add('fixed');
      } else {
        nav.classList.remove('fixed');
      }
    });
  }

  /**
   * Navegación móvil
   */
  function initMobileNavigation() {
    var $navbar = $('.navbar');
    var $navbarToggler = $('.navbar-toggler');
    var $navbarCollapse = $('.navbar-collapse');

    $(document).on('click', function (e) {
      if (!$navbar.is(e.target) && $navbar.has(e.target).length === 0) {
        if ($navbarCollapse.hasClass('show')) {
          $navbarToggler.click();
        }
      }
    });

    $('.navbar-nav .nav-link').on('click', function () {
      if ($navbarCollapse.hasClass('show')) {
        $navbarToggler.click();
      }
    });

    $(window).on('scroll', function () {
      if ($(window).scrollTop() > 100) {
        $navbar.addClass('navbar-scrolled');
      } else {
        $navbar.removeClass('navbar-scrolled');
      }
    });
  }

  /**
   * Formularios
   */
  function enhanceFormElements() {
    $('.form-item input[type="text"], .form-item input[type="email"], .form-item input[type="password"], .form-item textarea, .form-item select')
      .addClass('form-control');

    $('.form-item input[type="checkbox"], .form-item input[type="radio"]')
      .addClass('form-check-input');

    $('.form-control').on('blur', function () {
      validateField($(this));
    });

    $('form').on('submit', function () {
      var $submitBtn = $(this).find('input[type="submit"], button[type="submit"]');
      $submitBtn.addClass('loading').prop('disabled', true);

      if (!$submitBtn.find('.spinner-border').length) {
        $submitBtn.prepend('<span class="spinner-border spinner-border-sm me-2"></span>');
      }
    });
  }

  function validateField($field) {
    var value = $field.val().trim();
    var isValid = true;
    var errorMessage = '';

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
  }

  /**
   * Smooth scroll
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
   * Lazy loading
   */
  function initLazyLoading() {
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            observer.unobserve(img);
          }
        });
      });

      document.querySelectorAll('img[data-src]').forEach(function (img) {
        observer.observe(img);
      });
    }
  }

  /**
   * Analytics básico
   */
  function initAnalytics() {
    $('a[href^="http"]:not([href*="' + location.hostname + '"])').on('click', function () {
      if (typeof gtag !== 'undefined') {
        gtag('event', 'click', {
          event_category: 'external_link',
          event_label: $(this).attr('href')
        });
      }
    });
  }

})(jQuery, Drupal);
