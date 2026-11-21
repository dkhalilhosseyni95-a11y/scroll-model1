/**
 * MOON — admin panel behaviour.
 *
 * Tabs, colour pickers, media pickers, the drag-and-drop builders (homepage
 * sections, header element order, footer columns) and the confirmation guard
 * for destructive actions.
 */
(function ($) {
  'use strict';

  $(function () {
    /* Tabs ---------------------------------------------------------------- */
    var $tabs = $('[data-moon-tab]');

    $tabs.on('click', function (event) {
      event.preventDefault();

      var target = $(this).attr('href');

      $tabs.removeClass('nav-tab-active');
      $(this).addClass('nav-tab-active');
      $('.moon-tab-panel').removeClass('is-active');
      $(target).addClass('is-active');
    });

    /* Colour pickers ------------------------------------------------------ */
    if ($.fn.wpColorPicker) {
      $('.moon-color').wpColorPicker({
        change: function () {
          // keep the hidden/visible input value in sync with the picker
          var value = $(this).val();
          $(this).trigger('change');
          return value;
        }
      });
    }

    /* Media pickers ------------------------------------------------------- */
    $('[data-moon-media]').each(function () {
      var $wrap = $(this);
      var isFile = $wrap.data('type') === 'file';
      var $input = isFile ? $wrap.find('[data-moon-media-url]') : $wrap.find('[data-moon-media-input]');
      var $preview = $wrap.find('.moon-media__preview');
      var $remove = $wrap.find('[data-moon-media-remove]');
      var frame = null;

      $wrap.on('click', '[data-moon-media-select]', function (event) {
        event.preventDefault();

        if (frame) {
          frame.open();
          return;
        }

        frame = window.wp.media({
          title: isFile ? 'انتخاب فایل' : 'انتخاب تصویر',
          button: { text: 'استفاده از این فایل' },
          multiple: false
        });

        frame.on('select', function () {
          var attachment = frame.state().get('selection').first().toJSON();

          if (isFile) {
            $input.val(attachment.url).trigger('change');
            return;
          }

          var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

          $input.val(attachment.id).trigger('change');
          $preview.html($('<img>').attr({ src: url, alt: '' }));
          $remove.prop('hidden', false);
        });

        frame.open();
      });

      $remove.on('click', function (event) {
        event.preventDefault();
        $input.val('0').trigger('change');
        $preview.empty();
        $remove.prop('hidden', true);
      });
    });

    /* Homepage builder ---------------------------------------------------- */
    if ($.fn.sortable && $('[data-moon-builder]').length) {
      $('[data-moon-builder]').sortable({
        items: '[data-moon-row]',
        handle: '.moon-builder__grip',
        axis: 'y',
        update: function () {
          // Row names carry their order via DOM position; nothing else to do
          // because the server reads the submitted order.
          $(this).trigger('moon:reordered');
        }
      });
    }

    $('[data-moon-toggle-settings]').on('click', function () {
      var $button = $(this);
      var $row = $button.closest('[data-moon-row]');
      var $settings = $row.find('[data-moon-row-settings]');
      var open = $settings.prop('hidden') === false;

      $settings.prop('hidden', open);
      $button.attr('aria-expanded', open ? 'false' : 'true');
    });

    /* Header element order ------------------------------------------------ */
    $('[data-moon-sortable]').each(function () {
      var $list = $(this);
      var $input = $list.siblings('[data-moon-sortable-input]');

      function sync() {
        var value = [];

        $list.find('[data-moon-sortable-toggle]').each(function () {
          var $row = $(this).closest('[data-key]');
          value.push($row.data('key') + ':' + ($(this).is(':checked') ? '1' : '0'));
        });

        $input.val(value.join(','));
      }

      if ($.fn.sortable) {
        $list.sortable({ items: '.moon-sortable__row', handle: '.moon-sortable__grip', update: sync });
      }

      $list.on('change', '[data-moon-sortable-toggle]', sync);
      sync();
    });

    /* Footer columns ------------------------------------------------------ */
    var columnIndex = $('[data-moon-column]').length;

    function refreshColumnFields($column) {
      var type = $column.find('[data-moon-column-type]').val();

      $column.find('[data-moon-column-menu]').prop('hidden', type !== 'menu');
      $column.find('[data-moon-column-content]').prop('hidden', type !== 'links' && type !== 'text');
    }

    $('[data-moon-columns]').on('change', '[data-moon-column-type]', function () {
      refreshColumnFields($(this).closest('[data-moon-column]'));
    });

    $('[data-moon-columns]').on('click', '[data-moon-column-remove]', function (event) {
      event.preventDefault();

      if (!window.confirm('این ستون حذف شود؟')) {
        return;
      }

      $(this).closest('[data-moon-column]').remove();
    });

    $('[data-moon-column-add]').on('click', function (event) {
      event.preventDefault();

      var template = $('[data-moon-column-template]').html();

      if (!template) {
        return;
      }

      var html = template.replace(/__i__/g, 'new' + columnIndex);
      columnIndex++;

      var $column = $(html);

      $('[data-moon-columns]').append($column);
      refreshColumnFields($column);

      if ($.fn.sortable && !$('[data-moon-columns]').hasClass('ui-sortable')) {
        $('[data-moon-columns]').sortable({ items: '[data-moon-column]', handle: '.moon-builder__grip', axis: 'y' });
      }
    });

    if ($.fn.sortable && $('[data-moon-columns]').length) {
      $('[data-moon-columns]').sortable({ items: '[data-moon-column]', handle: '.moon-builder__grip', axis: 'y' });
    }

    /* Confirmation guard -------------------------------------------------- */
    $(document).on('click', '[data-moon-confirm]', function (event) {
      if (!window.confirm($(this).attr('data-moon-confirm'))) {
        event.preventDefault();
      }
    });

    /* Row title preview --------------------------------------------------- */
    $('[data-moon-columns]').on('input', 'input[name*="[title]"]', function () {
      $(this).closest('[data-moon-column]').find('.moon-builder__title').text($(this).val() || 'ستون بدون عنوان');
    });
  });
})(jQuery);
