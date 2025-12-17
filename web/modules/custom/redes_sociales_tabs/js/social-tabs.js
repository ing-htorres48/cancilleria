(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.socialTabs = {
    attach: function (context, settings) {
      once('social-tabs', '.social-tabs-block', context).forEach(function (element) {
        var $block = $(element);

        $block.find('.social-tabs-nav li').on('click', function () {
          var tabId = $(this).data('tab');

          // cambiar estado en nav
          $block.find('.social-tabs-nav li').removeClass('active');
          $(this).addClass('active');

          // cambiar contenido
          $block.find('.social-tab-pane').removeClass('active');
          $block.find('#' + tabId).addClass('active');
        });
      });
    }
  };
})(jQuery, Drupal, once);
