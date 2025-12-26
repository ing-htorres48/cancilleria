(function (Drupal, once, $) {
  Drupal.behaviors.sliderInterno = {
    attach(context) {
      once('slider-home', '.slick-slider-home', context).forEach((el) => {
        $(el).slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          dots: true,
          adaptiveHeight: true,
          autoplay: true,
          autoplaySpeed: 5000
        });
      });
    }
  };
})(Drupal, once, jQuery);
