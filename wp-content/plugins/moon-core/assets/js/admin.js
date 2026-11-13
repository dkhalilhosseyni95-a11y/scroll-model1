/**
 * MOON Core — admin panel behaviour: tabs, colour pickers, media pickers and
 * the homepage section order controls.
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
      $('.moon-color').wpColorPicker();
    }

    /* Media pickers ------------------------------------------------------- */
    $('[data-moon-media]').each(function () {
      var $wrap = $(this);
      var $input = $wrap.find('[data-moon-media-input]');
      var $preview = $wrap.find('.moon-media__preview');
      var $remove = $wrap.find('[data-moon-media-remove]');
      var frame = null;

      $wrap.on('click', '[data-moon-media-select]', function (event) {
        event.preventDefault();

        if (frame) {
          frame.open();
          return;
        }

        frame = wp.media({
          title: 'انتخاب تصویر',
          button: { text: 'استفاده از این تصویر' },
          multiple: false
        });

        frame.on('select', function () {
          var attachment = frame.state().get('selection').first().toJSON();
          var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

          $input.val(attachment.id);
          $preview.html($('<img>').attr({ src: url, alt: '' }));
          $remove.prop('hidden', false);
        });

        frame.open();
      });

      $remove.on('click', function (event) {
        event.preventDefault();
        $input.val('0');
        $preview.empty();
        $remove.prop('hidden', true);
      });
    });

    /* Section builder ----------------------------------------------------- */
    $('[data-moon-move]').on('click', function (event) {
      event.preventDefault();

      var $row = $(this).closest('[data-moon-row]');

      if ($(this).data('moon-move') === 'up') {
        $row.prev('[data-moon-row]').before($row);
      } else {
        $row.next('[data-moon-row]').after($row);
      }
    });
  });
})(jQuery);
