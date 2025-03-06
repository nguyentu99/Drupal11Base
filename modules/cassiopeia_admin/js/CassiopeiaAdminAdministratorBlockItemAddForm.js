(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.CassiopeiaAdminAdministratorBlockItemAddForm =  {
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
