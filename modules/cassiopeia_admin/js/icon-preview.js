(function ($, Drupal) {
  'use strict';

  function sanitizeIconClass(icon) {
    return String(icon || '').replace(/[^a-zA-Z0-9_\- ]/g, '').trim();
  }

  Drupal.behaviors.cassiopeiaAdminIconPreview = {
    attach: function (context) {
      $(context).find('input[name$="[icon]"]').each(function () {
        var $input = $(this);
        var $demo = $input.closest('form').find('#icon-demo i');
        if (!$demo.length) {
          return;
        }
        var update = function () {
          $demo.attr('class', sanitizeIconClass($input.val()));
        };
        $input.off('change.cassiopeiaAdminIcon input.cassiopeiaAdminIcon');
        $input.on('change.cassiopeiaAdminIcon input.cassiopeiaAdminIcon', update);
        update();
      });
    },
  };

})(jQuery, Drupal);
