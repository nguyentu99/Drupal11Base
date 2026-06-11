(function () {
    if (document.body.classList.contains('path-frontpage') && !window.location.hash) {
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        window.scrollTo(0, 0);
    }
})();

(function () {
    var videos = document.querySelectorAll('.cta-banner__video[data-autoplay-in-view]');
    if (!videos.length) return;

    if (!('IntersectionObserver' in window)) {
        videos.forEach(function (video) {
            video.play().catch(function () {});
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            var video = entry.target;
            if (entry.isIntersecting) {
                video.play().catch(function () {});
            } else {
                video.pause();
            }
        });
    }, { threshold: 0.2 });

    videos.forEach(function (video) {
        observer.observe(video);
    });
})();

(function () {
    var nav = document.querySelector('.site-header .main-nav');
    if (!nav) return;

    var backdrop = document.querySelector('.site-header__nav-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'site-header__nav-backdrop';
        document.body.appendChild(backdrop);
    }

    nav.addEventListener('show.bs.collapse', function () {
        backdrop.classList.add('is-visible');
        document.body.classList.add('is-mobile-nav-open');
    });

    nav.addEventListener('hide.bs.collapse', function () {
        backdrop.classList.remove('is-visible');
        document.body.classList.remove('is-mobile-nav-open');
    });

    backdrop.addEventListener('click', function () {
        if (typeof bootstrap === 'undefined' || !bootstrap.Collapse) {
            nav.classList.remove('show');
            backdrop.classList.remove('is-visible');
            document.body.classList.remove('is-mobile-nav-open');
            return;
        }

        var instance = bootstrap.Collapse.getInstance(nav);
        if (instance) {
            instance.hide();
        }
    });
})();

/* --------------------------------------------------------------------------
   2. index.html — stat-counter
   Section: Về chúng tôi / chỉ số (.stat-card__num[data-count])
   Chức năng: đếm số khi scroll vào viewport
   -------------------------------------------------------------------------- */
(function () {
    var nums = document.querySelectorAll('.stat-card__num[data-count]');
    if (!nums.length) return;

    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var duration = 2000;

    function runCounter(el) {
        var target = parseInt(el.getAttribute('data-count'), 10);
        if (isNaN(target)) return;

        if (reduced) {
            el.textContent = String(target);
            return;
        }

        var start = performance.now();

        function tick(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = String(Math.round(target * eased));
            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                el.textContent = String(target);
            }
        }

        requestAnimationFrame(tick);
    }

    var observer = new IntersectionObserver(
        function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                runCounter(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { root: null, threshold: 0.25 }
    );

    nums.forEach(function (el) {
        observer.observe(el);
    });
})();

/* --------------------------------------------------------------------------
   3. Trang dự án — project-tech-specs
   Trang: cnctech-ba-thien-1.html, ...
   Section: Bảng thông số kỹ thuật (.project-tech-specs)
   Chức năng: mở / thu gọn bảng
   -------------------------------------------------------------------------- */
(function () {
    var blocks = document.querySelectorAll('.project-tech-specs');
    if (!blocks.length) return;

    blocks.forEach(function (block) {
        var button = block.querySelector('.project-tech-specs__expand');
        var label = block.querySelector('.project-tech-specs__expand-label');

        if (!button || !label) return;

        var expandText = button.dataset.expandLabel || 'Mở rộng bảng';
        var collapseText = button.dataset.collapseLabel || 'Thu gọn';

        button.addEventListener('click', function () {
            var isExpanded = block.classList.toggle('is-expanded');

            button.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            label.textContent = isExpanded ? collapseText : expandText;
        });
    });
})();

/* --------------------------------------------------------------------------
   4. Marquee logos
   -------------------------------------------------------------------------- */
(function () {
    /* 4a. index.html — customers marquee (.customers__logos) */
    function initCustomersLogosMarquee() {
        document.querySelectorAll('.customers__logos').forEach(function (el) {
            if (el.dataset.marqueeReady === 'true') return;

            var logos = Array.from(el.querySelectorAll(':scope > .customers__logo'));
            if (!logos.length) return;

            var track = document.createElement('div');
            track.className = 'customers__logos-track';

            var groupA = document.createElement('div');
            groupA.className = 'customers__logos-group';
            logos.forEach(function (logo) {
                groupA.appendChild(logo);
            });

            track.appendChild(groupA);
            track.appendChild(groupA.cloneNode(true));
            el.appendChild(track);
            el.dataset.marqueeReady = 'true';
        });
    }

    /* 4b. Trang dự án — project-customers-marquee (.project-customers__logos) */
    function initProjectCustomersMarquee() {
        document.querySelectorAll('.project-customers__logos').forEach(function (el) {
            if (el.dataset.marqueeReady === 'true') return;

            var logos = Array.from(el.querySelectorAll(':scope > .project-customers__logo'));
            if (!logos.length) return;

            var track = document.createElement('div');
            track.className = 'project-customers__logos-track';

            var groupA = document.createElement('div');
            groupA.className = 'project-customers__logos-group';
            logos.forEach(function (logo) {
                groupA.appendChild(logo);
            });

            track.appendChild(groupA);
            track.appendChild(groupA.cloneNode(true));
            el.appendChild(track);
            el.dataset.marqueeReady = 'true';
        });
    }

    function initMarquees() {
        var scrollY = window.scrollY;
        initCustomersLogosMarquee();
        initProjectCustomersMarquee();
        requestAnimationFrame(function () {
            window.scrollTo(0, scrollY);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMarquees);
    } else {
        initMarquees();
    }
})();

/* --------------------------------------------------------------------------
   jQuery modules (cần jQuery; carousel cần thêm Owl Carousel)
   -------------------------------------------------------------------------- */
(function ($) {
    'use strict';

    var isFrontPage = document.body.classList.contains('path-frontpage') && !window.location.hash;

    /* ------------------------------------------------------------------------
       5. dich-vu.html — services-milestones
       Section: Cột mốc phát triển (.services-milestones)
       Chức năng: accordion mở/đóng từng giai đoạn
       ------------------------------------------------------------------------ */
    $('.services-milestones').each(function () {
        var $wrap = $(this);

        $wrap.on('click', '.services-milestones__head', function () {
            var $panel = $(this).closest('.services-milestones__panel');
            var isOpen = $panel.hasClass('is-open');

            $wrap.find('.services-milestones__panel').removeClass('is-open');
            $wrap.find('.services-milestones__head').attr('aria-expanded', 'false');

            if (!isOpen) {
                $panel.addClass('is-open');
                $(this).attr('aria-expanded', 'true');
            }
        });
    });

    /* ------------------------------------------------------------------------
       6. tin-tuc-doanh-nghiep.html — news-page filter
       Section: Bộ lọc danh mục tin (.news-page__filter)
       ------------------------------------------------------------------------ */
    $('.news-page__filter').on('click', function () {
        $('.news-page__filter').removeClass('is-active').attr('aria-selected', 'false');
        $(this).addClass('is-active').attr('aria-selected', 'true');
    });

    /* ------------------------------------------------------------------------
       7. doi-tac-khach-hang.html — customer-feature-video (mp4 inline)
       YouTube popup: themes/cassiopeia_theme/js/partner-video.js
       ------------------------------------------------------------------------ */
    $('.customer-feature__media-wrap').not('.customer-feature__media-wrap--youtube').each(function () {
        var $wrap = $(this);
        var $poster = $wrap.find('.customer-feature__poster');
        var $play = $wrap.find('.customer-feature__play');
        var video = $wrap.find('.customer-feature__video')[0];
        var posterMarkup = $poster.length ? $poster.prop('outerHTML') : '';
        var playMarkup = $play.length ? $play.prop('outerHTML') : '';

        if (!video) return;

        function playVideo() {
            $poster.remove();
            $play.remove();
            $wrap.addClass('is-playing');

            var playPromise = video.play();

            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () {
                    restorePoster();
                });
            }
        }

        function restorePoster() {
            $wrap.removeClass('is-playing');
            video.pause();
            video.currentTime = 0;

            if (posterMarkup && !$wrap.find('.customer-feature__poster').length) {
                $(posterMarkup).prependTo($wrap);
                $poster = $wrap.find('.customer-feature__poster');
            }

            if (playMarkup && !$wrap.find('.customer-feature__play').length) {
                $(playMarkup).appendTo($wrap);
                $play = $wrap.find('.customer-feature__play');
            }
        }

        $wrap.on('click', function (e) {
            if ($wrap.hasClass('is-playing')) return;
            if ($(e.target).closest('.customer-feature__video').length) return;

            e.preventDefault();
            playVideo();
        });

        video.addEventListener('ended', restorePoster);
    });

    if (typeof $.fn.owlCarousel !== 'function') {
        return;
    }

    /* Helpers dùng chung cho các carousel */
    function buildDots($wrap, count) {
        $wrap.empty();

        if (count <= 1) {
            $wrap.hide();
            return $();
        }

        $wrap.show();

        for (var i = 0; i < count; i++) {
            $wrap.append($('<button type="button">'));
        }

        return $wrap.children('button').first().addClass('is-active').end();
    }

    function buildGalleryDots($wrap, count) {
        $wrap.empty();

        if (count <= 1) {
            $wrap.hide();
            return $();
        }

        $wrap.show();

        for (var i = 0; i < count; i++) {
            $wrap.append(
                $('<button type="button">').attr('aria-label', 'Slide ' + (i + 1))
            );
        }

        return $wrap.children('button').first().addClass('is-active').end();
    }

    /* ------------------------------------------------------------------------
       8. index.html — services-showcase
       Section: Dịch vụ hero (.services-showcase)
       Chức năng: hover label đổi ảnh nền, phân trang label desktop
       ------------------------------------------------------------------------ */
    var ITEMS_PER_PAGE = 4;
    var DESKTOP_MQ = window.matchMedia('(min-width: 992px)');

    function initShowcase($showcase) {
        var slideEl = $showcase.find('.services-showcase__slide').get(0);
        if (!slideEl) return;

        var $labelsWrap = $showcase.find('.services-showcase__labels');
        var $prevBtn = $showcase.find('.services-showcase__nav-btn--prev');
        var $nextBtn = $showcase.find('.services-showcase__nav-btn--next');
        var bgs = slideEl.querySelectorAll('.services-showcase__bg');

        if (!$labelsWrap.length || bgs.length < 2) return;

        var defaultBg = slideEl.getAttribute('data-default-bg') || bgs[0].getAttribute('src');
        var active = 0;
        var busy = false;
        var pendingSrc = null;
        var labelIndex = -1;
        var pageIndex = 0;
        var pageCount = 0;

        function getLabels() {
            return Array.from(slideEl.querySelectorAll('.services-showcase__label[data-bg]'));
        }

        function preload(src) {
            return new Promise(function (resolve) {
                var img = new Image();
                img.onload = img.onerror = function () {
                    resolve();
                };
                img.src = src;
            });
        }

        function crossfade(src) {
            var currentEl = bgs[active];

            if (currentEl.getAttribute('src') === src && currentEl.classList.contains('is-visible')) {
                busy = false;
                pendingSrc = null;
                return;
            }

            busy = true;

            var nextIdx = active === 0 ? 1 : 0;
            var nextEl = bgs[nextIdx];

            preload(src).then(function () {
                nextEl.setAttribute('src', src);
                nextEl.classList.add('is-visible');
                currentEl.classList.remove('is-visible');
                active = nextIdx;
                busy = false;

                if (pendingSrc && pendingSrc !== src) {
                    crossfade(pendingSrc);
                } else {
                    pendingSrc = null;
                }
            });
        }

        function applyBg(src) {
            if (!src) return;

            var currentEl = bgs[active];
            if (currentEl.getAttribute('src') === src && currentEl.classList.contains('is-visible')) {
                pendingSrc = null;
                return;
            }

            pendingSrc = src;

            if (!busy) {
                crossfade(src);
            }
        }

        function clearLabelActive() {
            getLabels().forEach(function (label) {
                label.classList.remove('is-active');
            });
        }

        function activateLabel(index) {
            var labels = getLabels();
            if (!labels[index]) return;

            labelIndex = index;
            labels.forEach(function (label, i) {
                label.classList.toggle('is-active', i === index);
            });
            applyBg(labels[index].getAttribute('data-bg'));
        }

        function goToPage(nextPage, animate) {
            if (nextPage < 0 || nextPage >= pageCount) return;

            clearLabelActive();
            labelIndex = -1;
            pageIndex = nextPage;
            var owlIndex = DESKTOP_MQ.matches ? nextPage * ITEMS_PER_PAGE : nextPage;
            $labelsWrap.trigger('to.owl.carousel', [owlIndex, animate ? 450 : 0, true]);
            updateNavState();
        }

        function updateNavState() {
            if (!DESKTOP_MQ.matches || pageCount <= 1) {
                $prevBtn.addClass('is-disabled');
                $nextBtn.addClass('is-disabled');
                return;
            }

            $prevBtn.toggleClass('is-disabled', pageIndex <= 0);
            $nextBtn.toggleClass('is-disabled', pageIndex >= pageCount - 1);
        }

        function bindLabelInteractions() {
            getLabels().forEach(function (label, index) {
                label.onmouseenter = function () {
                    activateLabel(index);
                };

                label.onfocusin = function () {
                    activateLabel(index);
                };
            });
        }

        function destroyOwl() {
            if (!$labelsWrap.hasClass('owl-loaded')) return;

            $labelsWrap.trigger('destroy.owl.carousel');
            $labelsWrap.removeClass('owl-carousel owl-loaded');
            $labelsWrap.off('.owl.carousel');
        }

        function initOwl() {
            destroyOwl();
            bindLabelInteractions();

            clearLabelActive();
            labelIndex = -1;
            applyBg(defaultBg);

            $labelsWrap.addClass('owl-carousel');
            $labelsWrap.owlCarousel({
                loop: false,
                rewind: false,
                margin: 16,
                nav: false,
                dots: false,
                slideBy: 1,
                smartSpeed: 450,
                stagePadding: 0,
                responsive: {
                    0: {
                        items: 1,
                        slideBy: 1
                    },
                    992: {
                        items: ITEMS_PER_PAGE,
                        slideBy: ITEMS_PER_PAGE
                    }
                }
            });

            pageCount = DESKTOP_MQ.matches
                ? Math.ceil(getLabels().length / ITEMS_PER_PAGE)
                : getLabels().length;

            $labelsWrap.on('changed.owl.carousel initialized.owl.carousel', function (event) {
                if (!event.namespace) return;

                pageIndex = DESKTOP_MQ.matches
                    ? Math.floor((event.item.index || 0) / ITEMS_PER_PAGE)
                    : (event.item.index || 0);
                clearLabelActive();
                labelIndex = -1;
                updateNavState();
            });

            updateNavState();
        }
        
        slideEl.addEventListener('mouseleave', function () {
            var labels = getLabels();

            clearLabelActive();

            if (labelIndex >= 0 && labels[labelIndex]) {
                applyBg(labels[labelIndex].getAttribute('data-bg'));
                return;
            }

            applyBg(defaultBg);
        });

        $prevBtn.off('click.servicesShowcase').on('click.servicesShowcase', function () {
            if ($(this).hasClass('is-disabled')) return;
            goToPage(pageIndex - 1, true);
        });

        $nextBtn.off('click.servicesShowcase').on('click.servicesShowcase', function () {
            if ($(this).hasClass('is-disabled')) return;
            goToPage(pageIndex + 1, true);
        });

        initOwl();

        if (typeof DESKTOP_MQ.addEventListener === 'function') {
            DESKTOP_MQ.addEventListener('change', initOwl);
        } else if (typeof DESKTOP_MQ.addListener === 'function') {
            DESKTOP_MQ.addListener(initOwl);
        }
    }

    $('.services-showcase').each(function () {
        initShowcase($(this));
    });

    /* ------------------------------------------------------------------------
       9. project-carousel
       Trang: index, dich-vu, giai-phap-ha-tang-kcn, các trang KCN/dự án...
       Section: Carousel dự án (.project-carousel)
       ------------------------------------------------------------------------ */
    $('.project-carousel').each(function () {
        var $carousel = $(this);
        var $dotsWrap = $carousel.siblings('.project-carousel__dots').first();

        if (!$dotsWrap.length) {
            $dotsWrap = $carousel.parent().children('.project-carousel__dots').first();
        }

        var slideCount = $carousel.children().length;
        if (slideCount === 0) return;

        var $dots = buildDots($dotsWrap, slideCount);
        var isServiceCarousel = $carousel.hasClass('service-carousel');
        // Với carousel dịch vụ (3 slide), loop gây lộ mép slide clone khi hiển thị đủ 3 item
        var loop = slideCount > 1 && !(isServiceCarousel && slideCount <= 3);

        $carousel.owlCarousel({
            autoplay: true,
            autoplayHoverPause: true,
            autoplayTimeout: 5000,
            loop: loop,
            rewind: !loop && slideCount > 1,
            margin: 20,
            nav: false,
            dots: false,
            slideBy: 1,
            smartSpeed: 450,
            responsive: {
                0: { items: 1, margin: 16 },
                768: { items: 2, margin: 24 },
                1200: { items: 3, margin: 30 }
            }
        });

        function syncDots(event) {
            if (!event.namespace || !$dots.length) return;
            var index = event.relatedTarget.relative(event.item.index);
            $dots.removeClass('is-active').eq(index).addClass('is-active');
        }

        $carousel.on('initialized.owl.carousel changed.owl.carousel', syncDots);

        $dotsWrap.on('click', 'button', function () {
            $carousel.trigger('to.owl.carousel', [$(this).index(), 450, true]);
        });
    });

    /* ------------------------------------------------------------------------
       9b. service-solutions-carousel
       Trang: thiet-lap-he-thong-ky-thuat-noi-that-nha-xuong.html
       Section: Giải pháp toàn diện (.service-solutions-carousel)
       ------------------------------------------------------------------------ */
    $('.service-solutions-carousel').each(function () {
        var $carousel = $(this);
        var $dotsWrap = $carousel.siblings('.service-solutions__dots').first();
        var slideCount = $carousel.children().length;

        if (slideCount === 0) return;

        var $dots = buildDots($dotsWrap, slideCount);

        $carousel.owlCarousel({
            autoplay: true,
            autoplayHoverPause: true,
            autoplayTimeout: 5000,
            loop: slideCount > 1,
            margin: 30,
            nav: false,
            dots: false,
            slideBy: 1,
            smartSpeed: 450,
            responsive: {
                0: { items: 1, margin: 16 },
                768: { items: 2, margin: 24 },
                1200: { items: 2, margin: 30 }
            }
        });

        function syncSolutionDots(event) {
            if (!event.namespace || !$dots.length) return;
            var index = event.relatedTarget.relative(event.item.index);
            $dots.removeClass('is-active').eq(index).addClass('is-active');
        }

        $carousel.on('initialized.owl.carousel changed.owl.carousel', syncSolutionDots);

        $dotsWrap.on('click', 'button', function () {
            $carousel.trigger('to.owl.carousel', [$(this).index(), 450, true]);
        });
    });

    /* ------------------------------------------------------------------------
       10. index.html — customers-carousel
       Section: Khách hàng / testimonial (.customers-testimonials)
       Chức năng: carousel + scrollbar custom (.testimonial-card--scroll)
       ------------------------------------------------------------------------ */
    var $customersCarousel = $('.customers-testimonials');
    var $customersDots = $('.customers__dots button');

    if ($customersCarousel.length) {
        $customersCarousel.owlCarousel({
            autoplay: true,
            autoplayHoverPause: true,
            autoplayTimeout: 5000,
            loop: true,
            margin: 16,
            nav: false,
            dots: false,
            slideBy: 1,
            smartSpeed: 450,
            responsive: {
                0: { items: 1, margin: 16 },
                768: { items: 2, margin: 24 },
                1200: { items: 4, margin: 30 }
            }
        });

        function syncCustomerDots(event) {
            if (!event.namespace) return;
            var index = event.relatedTarget.relative(event.item.index);
            $customersDots.removeClass('is-active').eq(index).addClass('is-active');
        }

        $customersCarousel.on('initialized.owl.carousel changed.owl.carousel', syncCustomerDots);

        $customersDots.on('click', function () {
            $customersCarousel.trigger('to.owl.carousel', [$(this).index(), 450, true]);
        });

        function initScrollbars() {
            $('.testimonial-card--scroll').each(function () {
                var $card = $(this);
                var $text = $card.find('.testimonial-card__text');
                var $thumb = $card.find('.testimonial-card__scrollbar-thumb');
                var $track = $card.find('.testimonial-card__scrollbar');

                if (!$text.length || !$thumb.length || !$track.length) return;

                function updateThumb() {
                    var el = $text.get(0);
                    var trackH = $track.innerHeight();
                    var scrollH = el.scrollHeight;
                    var viewH = el.clientHeight;
                    var minThumb = 24;

                    if (scrollH <= viewH) {
                        $thumb.css({ height: trackH, transform: 'translateY(0)' });
                        return;
                    }

                    var thumbH = Math.max((viewH / scrollH) * trackH, minThumb);
                    var maxTop = trackH - thumbH;
                    var top = (el.scrollTop / (scrollH - viewH)) * maxTop;

                    $thumb.css({
                        height: thumbH,
                        transform: 'translateY(' + top + 'px)'
                    });
                }

                $text.on('scroll', updateThumb);
                $(window).on('resize', updateThumb);
                updateThumb();
            });
        }

        $customersCarousel.on('initialized.owl.carousel refreshed.owl.carousel', initScrollbars);
        initScrollbars();
    }

    /* ------------------------------------------------------------------------
       11. news-carousel
       Trang: index.html, tin-tuc-chi-tiet.html
       Section: Tin tức (.news-carousel)
       ------------------------------------------------------------------------ */
    var $newsCarousel = $('.news-carousel');
    var $newsDots = $('.news-carousel__dots button');

    if ($newsCarousel.length) {
        $newsCarousel.owlCarousel({
            autoplay: true,
            autoplayHoverPause: true,
            autoplayTimeout: 5000,
            loop: true,
            margin: 16,
            nav: false,
            dots: false,
            slideBy: 1,
            smartSpeed: 450,
            responsive: {
                0: { items: 1, margin: 16 },
                768: { items: 2, margin: 24 },
                1200: { items: 3, margin: 30 }
            }
        });

        function syncNewsDots(event) {
            if (!event.namespace) return;
            var index = event.relatedTarget.relative(event.item.index);
            $newsDots.removeClass('is-active').eq(index).addClass('is-active');
        }

        $newsCarousel.on('initialized.owl.carousel changed.owl.carousel', syncNewsDots);

        $newsDots.on('click', function () {
            $newsCarousel.trigger('to.owl.carousel', [$(this).index(), 450, true]);
        });
    }

    /* ------------------------------------------------------------------------
       12. Trang chi tiết dịch vụ — service-gallery | giai-phap-ha-tang-kcn, thiet-ke-*, thiet-lap-*
       9b. thiet-lap-he-thong-ky-thuat-noi-that-nha-xuong.html — service-solutions-carousel
       Section: Hình ảnh (.service-gallery)
       Chức năng: Bootstrap nav-tabs + Owl slider từng tab
       ------------------------------------------------------------------------ */
    var sliderOptions = {
        dots: false,
        loop: false,
        margin: 16,
        nav: false,
        slideBy: 1,
        smartSpeed: 450,
        responsive: {
            0: { items: 1 },
            576: { items: 2 },
            1200: { items: 4 }
        }
    };

    function updateGalleryArrows($panel, $slider) {
        var carousel = $slider.data('owl.carousel');
        if (!carousel) return;

        $panel.find('.service-gallery__arrow--prev').prop('disabled', carousel.current() <= carousel.minimum());
        $panel.find('.service-gallery__arrow--next').prop('disabled', carousel.current() >= carousel.maximum());
    }

    function bindGalleryArrows($panel, $slider) {
        $panel.find('.service-gallery__arrow--prev').off('click.serviceGallery').on('click.serviceGallery', function () {
            $slider.trigger('prev.owl.carousel');
        });

        $panel.find('.service-gallery__arrow--next').off('click.serviceGallery').on('click.serviceGallery', function () {
            $slider.trigger('next.owl.carousel');
        });

        $slider.off('initialized.owl.carousel changed.owl.carousel refreshed.owl.carousel')
            .on('initialized.owl.carousel changed.owl.carousel refreshed.owl.carousel', function () {
                updateGalleryArrows($panel, $slider);
            });
    }

    function initPanelSlider($panel) {
        var $slider = $panel.find('.service-gallery__slider');

        if (!$slider.length || $slider.hasClass('owl-loaded')) return;

        $slider.owlCarousel(sliderOptions);
        bindGalleryArrows($panel, $slider);
        updateGalleryArrows($panel, $slider);
    }

    function refreshPanelSlider($panel) {
        var $slider = $panel.find('.service-gallery__slider');

        if (!$slider.hasClass('owl-loaded')) {
            initPanelSlider($panel);
            return;
        }

        $slider.trigger('refresh.owl.carousel');
        updateGalleryArrows($panel, $slider);
    }

    $('.service-gallery').each(function () {
        var $gallery = $(this);
        var $panels = $gallery.find('.service-gallery__panel');

        initPanelSlider($panels.filter('.show.active').first());

        $gallery.on('shown.bs.tab', '[data-bs-toggle="tab"]', function (event) {
            var targetSelector = $(event.target).attr('data-bs-target');
            var $panel = targetSelector ? $gallery.find(targetSelector) : $();

            if ($panel.length) {
                refreshPanelSlider($panel);
            }
        });

        $(window).on('resize', function () {
            refreshPanelSlider($panels.filter('.show.active').first());
        });
    });

    /* ------------------------------------------------------------------------
       13. Trang dự án — project-gallery-carousel
       Section: Gallery ảnh dự án (.project-gallery)
       ------------------------------------------------------------------------ */
    $('.project-gallery').each(function () {
        var $gallery = $(this);
        var $carousel = $gallery.find('.project-gallery__carousel');
        var $dotsWrap = $gallery.find('.project-gallery__dots');
        var slideCount = $carousel.children('.project-gallery__slide').length;

        if (!$carousel.length || slideCount === 0) return;

        var $dots = buildGalleryDots($dotsWrap, slideCount);

        $carousel.owlCarousel({
            autoplay: true,
            autoplayHoverPause: true,
            autoplayTimeout: 5000,
            loop: slideCount > 1,
            margin: 16,
            nav: false,
            dots: false,
            slideBy: 1,
            smartSpeed: 450,
            responsive: {
                0: { items: 1, margin: 16 },
                768: { items: 2, margin: 16 },
                1200: { items: 3, margin: 16 }
            }
        });

        function syncProjectGalleryDots(event) {
            if (!event.namespace || !$dots.length) return;
            var index = event.relatedTarget.relative(event.item.index);
            $dots.removeClass('is-active').eq(index).addClass('is-active');
        }

        $carousel.on('initialized.owl.carousel changed.owl.carousel', syncProjectGalleryDots);

        $dotsWrap.on('click', 'button', function () {
            $carousel.trigger('to.owl.carousel', [$(this).index(), 450, true]);
        });
    });

    /* ------------------------------------------------------------------------
       14. bao-cao-thi-truong-chi-tiet.html — market-related-carousel
       Section: Bài liên quan (.market-related-carousel)
       ------------------------------------------------------------------------ */
    var $marketCarousel = $('.market-related-carousel');
    var $marketWrap = $('.market-related__inner');
    var $marketDots = $marketWrap.find('.market-related__dots button');

    if ($marketCarousel.length) {
        $marketCarousel.owlCarousel({
            autoplay: true,
            autoplayHoverPause: true,
            autoplayTimeout: 5000,
            loop: true,
            margin: 16,
            nav: false,
            dots: false,
            slideBy: 1,
            smartSpeed: 450,
            responsive: {
                0: { items: 1, margin: 16 },
                768: { items: 2, margin: 24 },
                1200: { items: 3, margin: 30 }
            }
        });

        function itemsPerDot() {
            var width = $(window).width();

            if (width >= 1200) return 3;
            if (width >= 768) return 2;
            return 1;
        }

        function syncMarketDots(event) {
            if (!event.namespace) return;

            var index = event.relatedTarget.relative(event.item.index);
            var dotIndex = Math.floor(index / itemsPerDot()) % $marketDots.length;

            $marketDots.removeClass('is-active').eq(dotIndex).addClass('is-active');
        }

        $marketCarousel.on('initialized.owl.carousel changed.owl.carousel', syncMarketDots);

        $marketDots.on('click', function () {
            var target = $(this).index() * itemsPerDot();
            $marketCarousel.trigger('to.owl.carousel', [target, 450, true]);
        });
    }

    if (isFrontPage) {
        window.scrollTo(0, 0);
        requestAnimationFrame(function () {
            window.scrollTo(0, 0);
        });
        window.addEventListener('load', function () {
            window.scrollTo(0, 0);
        }, { once: true });
    }
})(jQuery);
