/**
 * MOON — Customizer live preview.
 *
 * The panel owns every design value; the Customizer still exposes Site
 * Identity, so those two fields update live here.
 */
(function ($) {
  'use strict';

  if (!window.wp || !wp.customize) {
    return;
  }

  wp.customize('blogname', function (value) {
    value.bind(function (to) {
      $('.moon-brand__text').text(to);
      $('.moon-footer__brand').text(to);
    });
  });

  wp.customize('blogdescription', function (value) {
    value.bind(function (to) {
      $('.moon-brand__tagline').text(to);
    });
  });
})(jQuery);
