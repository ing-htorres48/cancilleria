(function (Drupal) {

  Drupal.behaviors.carouselExterior = {
    attach: function (context, settings) {

      const el = document.querySelector('#carousel-exterior');

      if (el && !el.dataset.initialized) {
        el.dataset.initialized = true;

        new Swiper('#carousel-exterior', {
          slidesPerView: 1,
          spaceBetween: 20,
          navigation: {
            nextEl: '#btn-swp-ext-next',
            prevEl: '#btn-swp-ext-prev'
          },
          breakpoints: {
            640: { slidesPerView: 2 },
            960: { slidesPerView: 3 }
          }
        });
      }
    }
  };

})(Drupal);
