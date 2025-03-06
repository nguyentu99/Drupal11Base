(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.CassiopeiaAdminAdministratorBlockEditForm =  {
    attach(context) {
      $("#edit-icon").change(function(){
        $("#icon-demo i").attr('class', $(this).val());
      });
      $("#edit-icon").each(function(){
        $("#icon-demo i").attr('class', $(this).val());
      });
    },
  }
})(jQuery, Drupal, drupalSettings);
