//jQuery(function($){
//  //$(".owl-carousel-duantrienkhai").owlCarousel({items: 1, autoPlay: true, pagination: false, navigation : false, lazyLoad : true, lazyEffect: 'aaa'});
//  $(".owl-carousel-duannoibat").owlCarousel({items: 5, autoPlay: true, pagination: false, navigation : false});
//});

jQuery(function($){
  $(document).ready(function(){
    customer_theme_owl($("#box-ykienkhachhang .view-content"), 1, true, false, true, false, 1, 1, false, 0);
    customer_theme_owl($("#box-tourhot .view-content"), 4, true, true, true, false, 1, 2, false, 10);
    //slide hotel
    var $sync1 = $(".owl-hotel-image"),
        $sync2 = $(".owl-hotel-image-thumb"),
        flag = false,
        duration = 300;

    $sync1
        .owlCarousel({
          items: 1,
          margin: 0,
          nav: true,
          dots: true,
          animateOut: 'fadeOut'
        })
        .on('changed.owl.carousel', function (e) {
          if (!flag) {
            flag = true;
            $sync2.trigger('to.owl.carousel', [e.item.index, duration, true]);
            flag = false;
          }
        });

    $sync2
        .owlCarousel({
          margin: 2,
          items: 6,
          nav: true,
          center: false,
          dots: true
        })
        .on('click', '.owl-item', function () {
          $sync1.trigger('to.owl.carousel', [$(this).index(), duration, true]);

        })
        .on('changed.owl.carousel', function (e) {
          if (!flag) {
            flag = true;
            $sync1.trigger('to.owl.carousel', [e.item.index, duration, true]);
            flag = false;
          }
        });


  });

  customer_theme_owl = function($element, items, loop, nav, autoplay, dots, items_mobile, items_table, autowidth, margin){
    if($element.size() == 0) return;
    var owl;
    if($element.children().size() > 1 && loop){
      owl = $element.owlCarousel({items: items, loop: true, nav: nav, animateOut: 'fadeOut', autoplay: autoplay, dots: dots, autoWidth:autowidth, responsiveClass: true, margin: margin, responsive: {0: {items: items_mobile}, 400: {items: items_table}, 768: {items: items}}});
    }else{
      owl = $element.owlCarousel({items: items, loop: false, nav: nav, animateOut: 'fadeOut', autoplay: autoplay, dots: dots, autoWidth:autowidth, responsiveClass: true, margin: margin, responsive: {0: {items: items_mobile}, 400: {items: items_table}, 768: {items: items}}});
    }
    return owl;
  }
});