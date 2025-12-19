/**
 * @file
 * Module page behaviors.
 */

(function ($, Drupal, debounce, once) {
  /**
   * Filters the micon list by a text input search string.
   *
   * Text search input: input.micon-filter-text
   * Target micon:      input.micon-filter-text[data-micon]
   * Source text:       .micon-filter-text.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.miconAdminFilterByText = {
    attach(context, settings) {
      const $input = $(once('micon-filter-text', 'input.micon-filter-text'));
      const $micon = $($input.attr('data-list'));
      let $columns;
      let searching = false;

      function filterList(e) {
        const query = $(e.target).val();
        // Case insensitive expression to find query at the beginning of a word.
        const re = new RegExp(`(\\b${query})+`, 'i');

        $columns.show();

        function showModuleRow(index, row) {
          const $row = $(row);
          const $sources = $row.find('.micon-filter-text');
          const nameMatch = $sources.text().search(re) !== -1;
          const tagMatch = $sources.data('tags').search(re) !== -1;
          $row.closest('li').toggle(nameMatch || tagMatch);
        }

        // Filter if the length of the query is at least 2 characters.
        if (query.length >= 2) {
          searching = true;
          $columns.each(showModuleRow);
        } else if (searching) {
          searching = false;
          $columns.show();
        }
      }

      function preventEnterKey(event) {
        if (event.which === 13) {
          event.preventDefault();
          event.stopPropagation();
        }
      }

      if ($micon.length) {
        $columns = $micon.find('li');

        $input.on({
          keyup: debounce(filterList, 200),
          keydown: preventEnterKey,
        });
      }
    },
  };
})(jQuery, Drupal, Drupal.debounce, once);
