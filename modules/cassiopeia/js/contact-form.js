(function (Drupal) {
  'use strict';

  Drupal.behaviors.cassiopeiaContactForm = {
    attach: function (context) {
      context.querySelectorAll('form.contact-form .contact-form__submit').forEach(function (submit) {
        if (submit.dataset.cassiopeiaIcon === '1' || submit.querySelector('.btn-see-more__icon')) {
          return;
        }
        submit.dataset.cassiopeiaIcon = '1';
        var icon = document.createElement('span');
        icon.className = 'btn-see-more__icon btn-see-more__icon--sm';
        icon.innerHTML = '<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>';
        submit.appendChild(icon);
      });
    }
  };
})(Drupal);
