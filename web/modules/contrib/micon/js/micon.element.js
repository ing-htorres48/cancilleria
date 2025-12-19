/**
 * @file
 * Initialize fontIconPicker.
 */

(function ($, once) {
  Drupal.behaviors.miconElement = {
    attach(context) {
      $(once('select-form-micon', 'select.form-micon')).fontIconPicker();
    },
  };
})(jQuery, once);
