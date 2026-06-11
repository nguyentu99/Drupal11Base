(function (Drupal, bootstrap, once) {
  'use strict';

  function getBootstrapModal() {
    var api = bootstrap || window.bootstrap;
    return api && api.Modal ? api.Modal : null;
  }

  function openModal(modalEl, iframeEl, embedUrl) {
    if (!modalEl || !iframeEl || !embedUrl) {
      return;
    }

    var separator = embedUrl.indexOf('?') >= 0 ? '&' : '?';
    iframeEl.setAttribute('src', embedUrl + separator + 'autoplay=1');

    var Modal = getBootstrapModal();
    if (Modal) {
      Modal.getOrCreateInstance(modalEl).show();
      return;
    }

    modalEl.classList.add('show');
    modalEl.style.display = 'block';
    modalEl.removeAttribute('aria-hidden');
    document.body.classList.add('modal-open');
  }

  function closeModal(modalEl, iframeEl) {
    if (iframeEl) {
      iframeEl.setAttribute('src', '');
    }
  }

  Drupal.behaviors.cassiopeiaPartnerVideo = {
    attach(context) {
      var modal = once('partner-video-modal', '#partnerVideoModal', context).shift();
      var iframe = modal ? modal.querySelector('.partner-video-modal__iframe') : null;

      if (modal && iframe) {
        modal.addEventListener('hidden.bs.modal', function () {
          closeModal(modal, iframe);
        });
      }

      once('partner-video-trigger', '.customer-feature__media-wrap--youtube', context).forEach(function (trigger) {
        function handleActivate(event) {
          if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
            return;
          }
          if (event.type === 'keydown') {
            event.preventDefault();
          }

          var embedUrl = trigger.getAttribute('data-youtube-embed');
          if (!embedUrl || !modal) {
            return;
          }

          event.preventDefault();
          openModal(modal, iframe, embedUrl);
        }

        trigger.addEventListener('click', handleActivate);
        trigger.addEventListener('keydown', handleActivate);
      });
    },
  };
})(Drupal, window.bootstrap, once);
