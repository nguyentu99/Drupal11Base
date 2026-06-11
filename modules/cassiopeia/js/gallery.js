/**
 * @file
 * Gallery page — Fancybox albums.
 */
(function () {
  'use strict';

  function getFancybox() {
    return window.Fancybox;
  }

  function openAlbum(page, trigger) {
    const Fancybox = getFancybox();
    if (!Fancybox || typeof Fancybox.show !== 'function') {
      return false;
    }

    const card = trigger.closest('.gallery-card');
    if (!card) {
      return false;
    }

    const nodes = card.querySelectorAll('.gallery-card__sources a[data-fancybox]');
    const items = Array.from(nodes).map((node) => ({
      src: node.getAttribute('href'),
      caption: node.getAttribute('data-caption') || '',
    }));

    if (!items.length) {
      return false;
    }

    Fancybox.show(items, { startIndex: 0 });
    return true;
  }

  function initGalleryPage(page) {
    page.addEventListener('click', (event) => {
      const trigger = event.target.closest('a[data-fancybox^="gallery-"]');
      if (!trigger || !page.contains(trigger)) {
        return;
      }

      event.preventDefault();
      openAlbum(page, trigger);
    });
  }

  function init() {
    if (!getFancybox()) {
      return;
    }

    document.querySelectorAll('.gallery-page').forEach((page) => {
      if (page.dataset.cassiopeiaGalleryInit) {
        return;
      }
      page.dataset.cassiopeiaGalleryInit = '1';
      initGalleryPage(page);

      const params = new URLSearchParams(window.location.search);
      const album = params.get('album');
      if (!album) {
        return;
      }

      const trigger = page.querySelector(`a[data-fancybox="gallery-${album}"]`);
      if (trigger) {
        openAlbum(page, trigger);
      }
    });
  }

  window.addEventListener('load', init);
})();
