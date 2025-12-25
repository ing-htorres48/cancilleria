(function (Drupal, once) {
  Drupal.behaviors.sliderInterno = {
    attach(context) {
      once('slider-interno', '.slick-slider', context).forEach((el) => {
        jQuery(el).slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          dots: true
        });
      });
    }
  };
})(Drupal, once);
