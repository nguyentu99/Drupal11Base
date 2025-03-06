(function ($) {
  var body = $("body");

  $(window).on("scroll", function () {
    if ($("#header").length > 0) {
      var _top = 0;
      $("#header").toggleClass(
        "active fixed animate__animated animate__slideInDown",
        $(window).scrollTop() > _top
      );
    }
  });

  function openCloseEl(elementId) {
    let el = $("#" + elementId);
    let display = el.css("display");
    if (display == "none") {
      el.css("display", "block");
      $("body").css("overflow", "hidden");
    } else {
      el.css("display", "none");
      $("body").css("overflow", "unset");
    }
  }

  $(".open-close-el").on("click", function () {
    openCloseEl("header-navigator");
  });

  $(".open-filter").on("click", function () {
    $(".desktop-filter-panel-overlay").addClass("filter-overlay-active");
    $(".um-sidebar").addClass("active");
    $("body").css("overflow", "hidden");
  });

  $(".close-filter").on("click", function () {
    $(".um-sidebar").removeClass("active");
    $(".desktop-filter-panel-overlay").removeClass("filter-overlay-active");
    $("body").css("overflow", "unset");
  });

  var showAll = $(".show-all-item");
  var hiddenAll = $(".hidden-all-item");
  hiddenAll.css("display", "none");
  var collapseItems = $(".schedule-collapse-content");

  showAll.on("click", function (e) {
    e.preventDefault();
    $(this).css("display", "none");
    collapseItems.each((ind, el) => $(el).collapse("show"));
    $(this).siblings("button").css("display", "block");
  });

  hiddenAll.on("click", function (e) {
    e.preventDefault();
    $(this).css("display", "none");
    collapseItems.each((ind, el) => $(el).collapse("hide"));
    $(this).siblings("button").css("display", "block");
  });

  var toggleSlider = $(".sidebar-toggle");

  toggleSlider.on("click", function (e) {
    e.preventDefault();
    body.toggleClass("sidebar-collapse");
  });
})(jQuery);
