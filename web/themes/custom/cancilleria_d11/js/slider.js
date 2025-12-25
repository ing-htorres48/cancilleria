(function (Drupal, once) {
  Drupal.behaviors.sliderSlick = {
    attach(context) {
      once('slider-slick', '.slick-slider', context).forEach((slider) => {
        jQuery(slider).slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          dots: true,
          autoplay: true,
          autoplaySpeed: 4000,
          adaptiveHeight: false
        });
      });
    }
  };
})(Drupal, once);
