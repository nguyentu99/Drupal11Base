/**
 * @file
 * Enhances IMCE UI with media-library layout helpers.
 */
(function (Drupal) {
  'use strict';

  var enhanced = false;

  function enhanceImce(imce) {
    if (!imce || !imce.fmEl || enhanced) {
      return;
    }
    enhanced = true;

    var fm = imce.fmEl;
    fm.classList.add('imce-modern-enhanced');

    var breadcrumb = document.createElement('div');
    breadcrumb.className = 'imce-modern-breadcrumb';
    fm.insertBefore(breadcrumb, imce.toolbarEl);

    var extra = document.createElement('div');
    extra.className = 'imce-modern-toolbar-extra';
    extra.innerHTML =
      '<button type="button" class="imce-modern-toolbar-btn imce-modern-filter-btn">' +
      Drupal.t('Filter (Everything)') + ' <span class="imce-modern-caret">&#9662;</span></button>' +
      '<button type="button" class="imce-modern-toolbar-btn imce-modern-viewin-btn">' +
      Drupal.t('View in (All media)') + ' <span class="imce-modern-caret">&#9662;</span></button>';
    imce.toolbarEl.appendChild(extra);

    var search = document.createElement('div');
    search.className = 'imce-modern-search';
    search.innerHTML =
      '<input type="search" placeholder="' +
      Drupal.t('Search file and folder') +
      '" autocomplete="off" />' +
      '<span class="imce-modern-search-icon" aria-hidden="true"></span>';
    imce.toolbarEl.appendChild(search);

    var searchInput = search.querySelector('input');
    searchInput.addEventListener('input', function () {
      filterItems(imce, searchInput.value);
    });

    extra.querySelector('.imce-modern-filter-btn').addEventListener('click', function () {
      searchInput.focus();
    });

    var subtoolbar = document.createElement('div');
    subtoolbar.className = 'imce-modern-subtoolbar';
    subtoolbar.innerHTML =
      '<span class="imce-modern-location">' +
      '<span class="imce-ficon imce-ficon-user" aria-hidden="true"></span>' +
      Drupal.t('All media') +
      '</span>' +
      '<div class="imce-modern-subtoolbar-actions">' +
      '<span class="imce-modern-chip">' + Drupal.t('Sort') + ' &#8645;</span>' +
      '<span class="imce-modern-chip">' + Drupal.t('Actions') + ' &#8942;</span>' +
      '<span class="imce-modern-view-toggle">' +
      '<button type="button" class="imce-modern-view-btn imce-modern-view-btn-grid" title="' +
      Drupal.t('Grid view') +
      '"></button>' +
      '<button type="button" class="imce-modern-view-btn imce-modern-view-btn-list is-active" title="' +
      Drupal.t('List view') +
      '"></button>' +
      '</span>' +
      '</div>';
    imce.contentEl.insertBefore(subtoolbar, imce.contentHeaderEl);

    var gridBtn = subtoolbar.querySelector('.imce-modern-view-btn-grid');
    var listBtn = subtoolbar.querySelector('.imce-modern-view-btn-list');

    gridBtn.addEventListener('click', function () {
      imce.contentEl.classList.add('thumbnail-grid');
      gridBtn.classList.add('is-active');
      listBtn.classList.remove('is-active');
    });

    listBtn.addEventListener('click', function () {
      imce.contentEl.classList.remove('thumbnail-grid');
      listBtn.classList.add('is-active');
      gridBtn.classList.remove('is-active');
    });

    function updateBreadcrumb() {
      var folder = imce.activeFolder;
      var label = Drupal.t('Bảng điều khiển') + ' / Media';
      if (folder) {
        var path = folder.getPath();
        if (path && path !== '.') {
          label += ' / ' + path;
        }
      }
      breadcrumb.textContent = label;
    }

    imce.bind('activateFolder', function () {
      updateBreadcrumb();
      searchInput.value = '';
      filterItems(imce, '');
    });

    updateBreadcrumb();
  }

  function filterItems(imce, query) {
    var folder = imce.activeFolder;
    if (!folder || typeof folder.getItems !== 'function') {
      return;
    }

    var q = String(query || '').toLowerCase().trim();
    folder.getItems().forEach(function (item) {
      if (!item.el) {
        return;
      }
      var visible = !q || String(item.name || '').toLowerCase().indexOf(q) !== -1;
      item.el.style.display = visible ? '' : 'none';
    });
  }

  function tryEnhance() {
    if (typeof window.imce === 'undefined') {
      return;
    }
    if (window.imce.fmEl) {
      enhanceImce(window.imce);
      return;
    }
    window.imce.bind('postinit', function () {
      enhanceImce(window.imce);
    });
  }

  Drupal.behaviors.cassiopeiaImceModern = {
    attach: function (context) {
      if (context !== document || !document.body.classList.contains('imce-page')) {
        return;
      }
      tryEnhance();
    },
  };
})(Drupal);
