/**
 * @file
 * Social Tabs block behavior.
 */

(function ($) {
  'use strict';
  if (typeof Drupal !== 'undefined') {
    Drupal.behaviors.socialTabsBlock = {
      attach: function (context) {
        var $blocks = $('.social-tabs-block', context);
        $blocks.each(function () {
          var $thisBlock = $(this);
          var $tabs = $thisBlock.find('.tab-nav-item');
          var $panes = $thisBlock.find('.social-tab-pane');
          // Click handler
          $tabs.click(function (e) {
            e.preventDefault();
            var tabId = $(this).data('tab');
            // Remove active from all
            $tabs.removeClass('active');
            $panes.removeClass('active');
            // Add active to clicked and corresponding pane
            $(this).addClass('active');
            $('#' + tabId).addClass('active');
          });
        });
      }
    };
  }
})(jQuery);
