(function () {
  var $;
  $ = this.jQuery || window.jQuery;
  var win = $(window),
    body = $("body"),
    doc = $(document);

  $.fn.nav_toggle = function () {
    var _this_ = $(this);

    _this_.find("ul>li").each(function (index, el) {
      if ($(el).find("ul li").length > 0)
        $(el).prepend('<button type="button" class="acd-drop"></button>');
    });

    _this_.on("click", ".acd-drop", function (e) {
      e.preventDefault();
      var ul = $(this).nextAll("ul");
      if (ul.is(":hidden") === true) {
        ul.parent("li").parent("ul").children("li").children("ul").slideUp(180);
        ul.parent("li")
          .parent("ul")
          .children("li")
          .children(".acd-drop")
          .removeClass("active");
        $(this).addClass("active");
        ul.slideDown(180);
      } else {
        $(this).removeClass("active");
        ul.slideUp(180);
      }
    });
  };

  $.fn.mb_menu = function (options) {
    var settings = $.extend(
        {
          open: ".open-nav",
        },
        options
      ),
      this_ = $(this);
    var m_toggle_nav = $(".mb-menu-toggle");

    m_toggle_nav.click(function (e) {
      e.preventDefault();
      nav_close();
    });

    var nav_open = function () {
      m_toggle_nav.addClass("active");
      body.append('<div class="m-nav-over"></div>').css("overflow", "hidden");
      body.css("overflow", "hidden");
    };

    var nav_close = function () {
      m_toggle_nav.removeClass("active");
      body.children(".m-nav-over").remove();
      body.css("overflow", "");
    };

    doc
      .on("click", settings.open, function (e) {
        e.preventDefault();
        if (win.width() <= 1199) nav_open();
      })
      .on("click", ".m-nav-over", function (e) {
        e.preventDefault();
        nav_close();
      });

    m_toggle_nav.nav_toggle();
  };

  $.fn.sidebar_toggle = function (e) {
    var _this_ = $(this);

    _this_.find(".um-sidebar-block-title .icon").each(function (ind, el) {
      $(el).on("click", function (e) {
        e.preventDefault();
        var _parent = $(this).parent();
        var content = _parent.siblings(".um-sidebar-block-content");

        if (content.is(":hidden")) {
          content.slideDown(800);
          _parent.parent().addClass("open");
        } else {
          _parent.parent().removeClass("open");
          content.slideUp(800);
        }
      });
    });
  };

  $(".um-sidebar").sidebar_toggle();
})();

jQuery(function ($) {
  var win = $(window),
    body = $("body"),
    doc = $(document);

  var UI = {
    header: function (fixed) {
      var elem = $("header"),
        offset = 200;

      var toggleFixed = function (screenBreakpoints = 0) {
        window.scroll(function () {
          if (win.innerWidth() > screenBreakpoints) {
            elem.toggleClass("fixed", win.scrollTop() >= offset);
          }
        });
      };

      if (fixed) return toggleFixed();
    },

    menuBar: function (fixed) {},

    backToTop: function () {
      var backTop = $(".back-to-top"),
        offset = 800;

      backTop.click(function () {
        $("html,  body").animate({ scrollTop: 0 }, 800);
      });

      if (win.scrollTop() >= offset) {
        backTop.fadeIn(200);
      }

      window.scroll(function () {
        if (win.scrollTop() > offset) {
          backTop.fadeIn(200);
        } else {
          backTop.fadeOut(200);
        }
      });
    },

    fancyBox: function () {
      var elem = $("[data-fancyBox]");
      elem.fancybox({
        // Options will go here
        buttons: ["close"],
        wheel: false,
        transitionEffect: "slide",
        // thumbs          : false,
        // hash            : false,
        loop: true,
        // keyboard        : true,
        toolbar: false,
        // animationEffect : false,
        // arrows          : true,
        clickContent: false,
      });
    },

    readMore: function () {
      if ($(".read-more").length) {
        var text = 'Xem thêm <i class="fa fa-angle-down"></i>';
        $(".read-more").on("click", function (e) {
          if ($(this).html() === text) {
            $(this).prev().addClass("show");
            $(this).html('Rút gọn <i class="fa fa-angle-up"></i>');
          } else {
            $(this).prev().removeClass("show");
            $(this).html(text);
          }
        });
      }
    },

    psy: function () {
      var btn = ".psy-link";
      sec = $(".psy-section");
      pane = ".psy-pane";

      doc.on("click", btn, function (e) {
        e.preventDefault();
        $(this).parents(pane).find(btn).removeClass("active");
        $(this).addClass("active");
        if ($("header").hasClass("fixed")) {
          $("html, body").animate(
            {
              scrollTop:
                $($(this).attr("href")).offset().top -
                $("header").innerHeight(),
            },
            1200
          );
        } else {
          $("html, body").animate(
            {
              scrollTop: $($(this).attr("href")).offset().top,
            },
            1200
          );
        }
      });

      win.on("scroll", function (e) {});
    },

    drop: function (e) {
      $(".custom-drop").each(function () {
        var _this_ = $(this);
        var label = _this_.children(".label");
        var ct = _this_.children("ul");
        ct.slideUp(150);
        var item = ct.children("li").children("a.drop-item");

        _this_.on("click", function () {
          ct.slideDown(150);
          label.toggleClass("active");
        });

        item.on("click", function (e) {
          e.stopPropagation();
          label.html($(this).html());
          _this_.children("ul").slideUp(150);
          return false;
        });

        win.on("click", function (e) {
          if (_this_.has(e.target).length === 0 && !_this_.is(e.target)) {
            _this_.children("ul").slideUp(150);
            label.removeClass("active");
          }
        });
      });
    },

    toggle: function (e) {
      var ani = 100;
      $(["data-show"]).each(function (index, el) {
        var ct = $($(el).attr("data-show"));
        $(el).click(function (e) {
          e.preventDefault();
          ct.fadeToggle(ani);
        });
      });

      win.click(function (e) {
        $(["data-show"]).each(function (index, el) {
          var ct = $($(el).attr("data-show"));
        });
        if (
          ct.has(e.target).length === 0 &&
          !ct.is(e.target) &&
          $(el).has(e.target).length == 0 &&
          !$(el).is(e.target)
        ) {
          ct.fadeOut(ani);
        }
      });
    },

    initSlider: function (element, options) {
      var _el = $(element),
        slider;

      var defaultOptions = {
        loop: true,
        nav: true,
        margin: options?.margin || 30,
        navText: [
          '<i class="fal fa-arrow-left fa-fw"></i>',
          '<i class="fal fa-arrow-right fa-fw"></i>',
        ],
        dots: true,
        responsive: {
          //breakpoint from 0 up
          0: {
            items: 1,
            margin: 0,
          },
          // 768: {
          //   items: 2,
          //   margin: 10,
          // },
          991: {
            items: options.items || 4,
          },
        },
      };

      if (typeof options !== "object") return;

      var optionMerged = { ...defaultOptions, ...options };

      if (_el.length > 0) {
        slider = _el.owlCarousel(optionMerged);
      }

      if (options.navCustom) {
        var customNextBtn = document.createElement("button");
        customNextBtn.classList.add("customNextBtn", "customNavSlide");
        customNextBtn.innerHTML = '<i class="fal fa-arrow-right fa-fw"></i>';

        var customPrevBtn = document.createElement("button");
        customPrevBtn.classList.add("customPrevBtn", "customNavSlide");
        customPrevBtn.innerHTML = '<i class="fal fa-arrow-left fa-fw"></i>';

        _el.parents(".slider-cover").append(customNextBtn, customPrevBtn);

        customPrevBtn.addEventListener("click", function (e) {
          e.preventDefault();
          slider.trigger("prev.owl.carousel");
        });

        customNextBtn.addEventListener("click", function (e) {
          e.preventDefault();
          slider.trigger("next.owl.carousel");
        });
      }
    },

    ready: function () {
      UI.drop();
      UI.psy();
      UI.drop();
      UI.initSlider(".main-slider", {
        dots: false,
        nav: false,
        loop: true,
        items: 1,
        margin: 0,
        autoplay: true,
        animateOut: "fadeOut",
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
      });
      UI.initSlider(".blog-slide", {
        dots: true,
        nav: false,
        navCustom: false,
        items: 3,
        margin: 30,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        animateOut: "fadeOut",
      });
    },
  };
  UI.ready();
});
