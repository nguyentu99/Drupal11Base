jQuery(function($){
  $("#edit-icon").change(function(){
    $("#icon-demo i").attr('class', 'fa ' + $(this).val());
  });

  $("#edit-icon").each(function(){
    $("#icon-demo i").attr('class', 'fa ' + $(this).val());
  });
});
