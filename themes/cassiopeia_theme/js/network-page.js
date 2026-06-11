(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.cassiopeiaNetworkFilter = {
    attach(context) {
      once('cassiopeia-network-filter', '.network-page__filter', context).forEach((select) => {
        select.addEventListener('change', () => {
          const value = select.value;
          const panel = select.closest('.network-page__panel');
          if (!panel) {
            return;
          }
          panel.querySelectorAll('.network-region-block[data-province]').forEach((block) => {
            const show = !value || block.dataset.province === value;
            block.hidden = !show;
          });
        });
      });
    },
  };
})(Drupal, once);
