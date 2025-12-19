/**
 * Slick Carousel Initialization
 * This file handles the initialization of Slick carousels on the page
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.slickInit = {
    attach: function (context, settings) {
      // Initialize default slick carousels
      $('.slick-container', context).once('slick-init').each(function () {
        var $carousel = $(this);
        var options = {
          // Basic settings
          dots: true,
          arrows: true,
          infinite: true,
          speed: 500,
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 3000,
          pauseOnHover: true,
          fade: false,

          // Responsive settings
          responsive: [
            {
              breakpoint: 768,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
              }
            },
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 2,
              }
            },
            {
              breakpoint: 1400,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
              }
            }
          ]
        };

        // Merge with data attributes if present
        if ($carousel.data('slick-options')) {
          $.extend(options, $carousel.data('slick-options'));
        }

        // Initialize Slick
        $carousel.slick(options);

        // Optional: Add custom events
        $carousel.on('beforeChange', function (event, slick, currentSlide, nextSlide) {
          console.log('Slick carousel changing from slide ' + currentSlide + ' to ' + nextSlide);
        });

        $carousel.on('afterChange', function (event, slick, currentSlide) {
          console.log('Slick carousel now on slide ' + currentSlide);
        });
      });

      // Initialize carousels with custom selector
      $('.js-slick-carousel', context).once('slick-custom').each(function () {
        var $carousel = $(this);
        $carousel.slick({
          dots: true,
          arrows: true,
          infinite: true,
          speed: 500,
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 4000,
        });
      });
    }
  };

})(jQuery, Drupal);
