/**
 * @file
 * Display Drupal status and AJAX messages as Bootstrap toasts.
 */
(function (Drupal, bootstrap, once) {
  'use strict';

  const TYPE_MAP = {
    status: { toastClass: 'toast-success', autohide: true },
    warning: { toastClass: 'toast-warning', autohide: true },
    error: { toastClass: 'toast-danger', autohide: false },
    info: { toastClass: 'toast-info', autohide: true },
  };

  const DEFAULT_DELAY = 8000;

  function getToastContainer() {
    let container = document.querySelector('[data-drupal-messages-toast-container]');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container position-fixed top-0 end-0 p-3 app-toast-container';
      container.setAttribute('aria-live', 'polite');
      container.setAttribute('aria-atomic', 'true');
      container.setAttribute('data-drupal-messages-toast-container', '');
      const queue = document.createElement('div');
      queue.className = 'toast-message-queue';
      queue.setAttribute('data-drupal-messages', '');
      container.appendChild(queue);
      document.body.appendChild(container);
    }
    return container;
  }

  function showToast(toastEl) {
    if (!toastEl || typeof bootstrap === 'undefined' || !bootstrap.Toast) {
      return;
    }
    bootstrap.Toast.getOrCreateInstance(toastEl).show();
  }

  function buildToastElement(text, type, id) {
    const config = TYPE_MAP[type] || TYPE_MAP.info;
    const labels = Drupal.Message.getMessageTypeLabels();
    const label = labels[type] || labels.status || 'Message';
    const toast = document.createElement('div');
    toast.className = `toast fade ${config.toastClass}`;
    toast.setAttribute('role', type === 'error' || type === 'warning' ? 'alert' : 'status');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.setAttribute('data-bs-autohide', config.autohide ? 'true' : 'false');
    toast.setAttribute('data-bs-delay', String(DEFAULT_DELAY));
    toast.setAttribute('data-drupal-message-id', id);
    toast.setAttribute('data-drupal-message-type', type);

    const header = document.createElement('div');
    header.className = 'toast-header';
    header.innerHTML = '<strong class="me-auto"></strong>';
    header.querySelector('strong').textContent = label;

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close';
    close.setAttribute('data-bs-dismiss', 'toast');
    close.setAttribute('aria-label', Drupal.t('Close'));
    header.appendChild(close);

    const body = document.createElement('div');
    body.className = 'toast-body';
    body.innerHTML = text;

    toast.appendChild(header);
    toast.appendChild(body);

    return toast;
  }

  Drupal.behaviors.cassiopeiaThemeToastMessages = {
    attach(context) {
      once('cassiopeia-theme-toast-show', '.toast-container .toast', context).forEach((toastEl) => {
        showToast(toastEl);
      });
    },
  };

  Drupal.theme.message = ({ text }, { type, id }) => {
    return buildToastElement(text, type, id);
  };
})(Drupal, window.bootstrap, once);
