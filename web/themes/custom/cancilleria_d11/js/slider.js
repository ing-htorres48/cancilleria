(function (Drupal, once, $) {
  Drupal.behaviors.sliderInterno = {
    attach(context) {
      once('slider-interno', '.slick-slider', context).forEach((el) => {
        $(el).slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          dots: true,
          adaptiveHeight: true
        });
      });
    }
  };
})(Drupal, once, jQuery);
