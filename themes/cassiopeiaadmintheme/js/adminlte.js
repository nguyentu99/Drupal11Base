/*!
 * AdminLTE v4.0.0-beta2 (https://adminlte.io)
 * Copyright 2014-2025 Colorlib <https://colorlib.com>
 * Licensed under MIT (https://github.com/ColorlibHQ/AdminLTE/blob/master/LICENSE)
 */
(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('jquery')) :
    typeof define === 'function' && define.amd ? define(['exports', 'jquery'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.adminlte = {}, global.jQuery));
})(this, (function (exports, jQuery) { 'use strict';

    const domContentLoadedCallbacks = [];
    const onDOMContentLoaded = (callback) => {
        if (document.readyState === 'loading') {
            // add listener on the first call when the document is in loading state
            if (!domContentLoadedCallbacks.length) {
                document.addEventListener('DOMContentLoaded', () => {
                    for (const callback of domContentLoadedCallbacks) {
                        callback();
                    }
                });
            }
            domContentLoadedCallbacks.push(callback);
        }
        else {
            callback();
        }
    };
    /* SLIDE UP */
    const slideUp = (target, duration = 500) => {
        target.style.transitionProperty = 'height, margin, padding';
        target.style.transitionDuration = `${duration}ms`;
        target.style.boxSizing = 'border-box';
        target.style.height = `${target.offsetHeight}px`;
        target.style.overflow = 'hidden';
        window.setTimeout(() => {
            target.style.height = '0';
            target.style.paddingTop = '0';
            target.style.paddingBottom = '0';
            target.style.marginTop = '0';
            target.style.marginBottom = '0';
        }, 1);
        window.setTimeout(() => {
            target.style.display = 'none';
            target.style.removeProperty('height');
            target.style.removeProperty('padding-top');
            target.style.removeProperty('padding-bottom');
            target.style.removeProperty('margin-top');
            target.style.removeProperty('margin-bottom');
            target.style.removeProperty('overflow');
            target.style.removeProperty('transition-duration');
            target.style.removeProperty('transition-property');
        }, duration);
    };
    /* SLIDE DOWN */
    const slideDown = (target, duration = 500) => {
        target.style.removeProperty('display');
        let { display } = window.getComputedStyle(target);
        if (display === 'none') {
            display = 'block';
        }
        target.style.display = display;
        const height = target.offsetHeight;
        target.style.overflow = 'hidden';
        target.style.height = '0';
        target.style.paddingTop = '0';
        target.style.paddingBottom = '0';
        target.style.marginTop = '0';
        target.style.marginBottom = '0';
        window.setTimeout(() => {
            target.style.boxSizing = 'border-box';
            target.style.transitionProperty = 'height, margin, padding';
            target.style.transitionDuration = `${duration}ms`;
            target.style.height = `${height}px`;
            target.style.removeProperty('padding-top');
            target.style.removeProperty('padding-bottom');
            target.style.removeProperty('margin-top');
            target.style.removeProperty('margin-bottom');
        }, 1);
        window.setTimeout(() => {
            target.style.removeProperty('height');
            target.style.removeProperty('overflow');
            target.style.removeProperty('transition-duration');
            target.style.removeProperty('transition-property');
        }, duration);
    };
    /* TOGGLE */
    const slideToggle = (target, duration = 500) => {
        if (window.getComputedStyle(target).display === 'none') {
            slideDown(target, duration);
            return;
        }
        slideUp(target, duration);
    };

    /**
     * --------------------------------------------
     * @file AdminLTE layout.ts
     * @description Layout for AdminLTE.
     * @license MIT
     * --------------------------------------------
     */
    /**
     * ------------------------------------------------------------------------
     * Constants
     * ------------------------------------------------------------------------
     */
    const CLASS_NAME_HOLD_TRANSITIONS = 'hold-transition';
    const CLASS_NAME_APP_LOADED = 'app-loaded';
    /**
     * Class Definition
     * ====================================================
     */
    class Layout {
        constructor(element) {
            this._element = element;
        }
        holdTransition() {
            let resizeTimer;
            window.addEventListener('resize', () => {
                document.body.classList.add(CLASS_NAME_HOLD_TRANSITIONS);
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    document.body.classList.remove(CLASS_NAME_HOLD_TRANSITIONS);
                }, 400);
            });
        }
    }
    onDOMContentLoaded(() => {
        const data = new Layout(document.body);
        data.holdTransition();
        setTimeout(() => {
            document.body.classList.add(CLASS_NAME_APP_LOADED);
        }, 400);
    });

    /**
     * --------------------------------------------
     * @file AdminLTE push-menu.ts
     * @description Push menu for AdminLTE.
     * @license MIT
     * --------------------------------------------
     */
    /**
     * ------------------------------------------------------------------------
     * Constants
     * ------------------------------------------------------------------------
     */
    const DATA_KEY$5 = 'lte.push-menu';
    const EVENT_KEY$5 = `.${DATA_KEY$5}`;
    const EVENT_OPEN = `open${EVENT_KEY$5}`;
    const EVENT_COLLAPSE = `collapse${EVENT_KEY$5}`;
    const CLASS_NAME_SIDEBAR_MINI = 'sidebar-mini';
    const CLASS_NAME_SIDEBAR_COLLAPSE = 'sidebar-collapse';
    const CLASS_NAME_SIDEBAR_OPEN = 'sidebar-open';
    const CLASS_NAME_SIDEBAR_EXPAND = 'sidebar-expand';
    const CLASS_NAME_SIDEBAR_OVERLAY = 'sidebar-overlay';
    const CLASS_NAME_MENU_OPEN$1 = 'menu-open';
    const SELECTOR_APP_SIDEBAR = '.app-sidebar';
    const SELECTOR_SIDEBAR_MENU = '.sidebar-menu';
    const SELECTOR_NAV_ITEM$1 = '.nav-item';
    const SELECTOR_NAV_TREEVIEW$1 = '.nav-treeview';
    const SELECTOR_APP_WRAPPER = '.app-wrapper';
    const SELECTOR_SIDEBAR_EXPAND = `[class*="${CLASS_NAME_SIDEBAR_EXPAND}"]`;
    const SELECTOR_SIDEBAR_TOGGLE = '[data-lte-toggle="sidebar"]';
    const Defaults$1 = {
        sidebarBreakpoint: 992
    };
    /**
     * Class Definition
     * ====================================================
     */
    class PushMenu {
        constructor(element, config) {
            this._element = element;
            this._config = Object.assign(Object.assign({}, Defaults$1), config);
        }
        // eslint-disable-next-line no-warning-comments
        // TODO
        menusClose() {
            const navTreeview = document.querySelectorAll(SELECTOR_NAV_TREEVIEW$1);
            navTreeview.forEach(navTree => {
                navTree.style.removeProperty('display');
                navTree.style.removeProperty('height');
            });
            const navSidebar = document.querySelector(SELECTOR_SIDEBAR_MENU);
            const navItem = navSidebar === null || navSidebar === void 0 ? void 0 : navSidebar.querySelectorAll(SELECTOR_NAV_ITEM$1);
            if (navItem) {
                navItem.forEach(navI => {
                    navI.classList.remove(CLASS_NAME_MENU_OPEN$1);
                });
            }
        }
        expand() {
            const event = new Event(EVENT_OPEN);
            const sidebar = document === null || document === void 0 ? void 0 : document.querySelector(SELECTOR_APP_SIDEBAR);
            if (sidebar) {
                sidebar.classList.add('shadow');
            }
            document.body.classList.remove(CLASS_NAME_SIDEBAR_COLLAPSE);
            document.body.classList.add(CLASS_NAME_SIDEBAR_OPEN);
            this._element.dispatchEvent(event);
        }
        collapse() {
            const event = new Event(EVENT_COLLAPSE);
            const sidebar = document === null || document === void 0 ? void 0 : document.querySelector(SELECTOR_APP_SIDEBAR);
            if (sidebar) {
                sidebar.classList.remove('shadow');
            }
            document.body.classList.remove(CLASS_NAME_SIDEBAR_OPEN);
            document.body.classList.add(CLASS_NAME_SIDEBAR_COLLAPSE);
            this._element.dispatchEvent(event);
        }
        addSidebarBreakPoint() {
            var _a, _b, _c;
            const sidebarExpandList = (_b = (_a = document.querySelector(SELECTOR_SIDEBAR_EXPAND)) === null || _a === void 0 ? void 0 : _a.classList) !== null && _b !== void 0 ? _b : [];
            const sidebarExpand = (_c = Array.from(sidebarExpandList).find(className => className.startsWith(CLASS_NAME_SIDEBAR_EXPAND))) !== null && _c !== void 0 ? _c : '';
            const sidebar = document.getElementsByClassName(sidebarExpand)[0];
            const sidebarContent = window.getComputedStyle(sidebar, '::before').getPropertyValue('content');
            this._config = Object.assign(Object.assign({}, this._config), { sidebarBreakpoint: Number(sidebarContent.replace(/[^\d.-]/g, '')) });
            if (window.innerWidth <= this._config.sidebarBreakpoint) {
                this.collapse();
            }
            else {
                if (!document.body.classList.contains(CLASS_NAME_SIDEBAR_MINI)) {
                    this.expand();
                }
                if (document.body.classList.contains(CLASS_NAME_SIDEBAR_MINI) && document.body.classList.contains(CLASS_NAME_SIDEBAR_COLLAPSE)) {
                    this.collapse();
                }
            }
        }
        toggle() {
            if (document.body.classList.contains(CLASS_NAME_SIDEBAR_COLLAPSE)) {
                this.expand();
            }
            else {
                this.collapse();
            }
        }
        init() {
            this.addSidebarBreakPoint();
        }
    }
    /**
     * ------------------------------------------------------------------------
     * Data Api implementation
     * ------------------------------------------------------------------------
     */
    onDOMContentLoaded(() => {
        var _a;
        const sidebar = document === null || document === void 0 ? void 0 : document.querySelector(SELECTOR_APP_SIDEBAR);
        if (sidebar) {
            const data = new PushMenu(sidebar, Defaults$1);
            data.init();
            window.addEventListener('resize', () => {
                data.init();
            });
        }
        const sidebarOverlay = document.createElement('div');
        sidebarOverlay.className = CLASS_NAME_SIDEBAR_OVERLAY;
        (_a = document.querySelector(SELECTOR_APP_WRAPPER)) === null || _a === void 0 ? void 0 : _a.append(sidebarOverlay);
        sidebarOverlay.addEventListener('touchstart', event => {
            event.preventDefault();
            const target = event.currentTarget;
            const data = new PushMenu(target, Defaults$1);
            data.collapse();
        }, { passive: true });
        sidebarOverlay.addEventListener('click', event => {
            event.preventDefault();
            const target = event.currentTarget;
            const data = new PushMenu(target, Defaults$1);
            data.collapse();
        });
        const fullBtn = document.querySelectorAll(SELECTOR_SIDEBAR_TOGGLE);
        fullBtn.forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                let button = event.currentTarget;
                if ((button === null || button === void 0 ? void 0 : button.dataset.lteToggle) !== 'sidebar') {
                    button = button === null || button === void 0 ? void 0 : button.closest(SELECTOR_SIDEBAR_TOGGLE);
                }
                if (button) {
                    event === null || event === void 0 ? void 0 : event.preventDefault();
                    const data = new PushMenu(button, Defaults$1);
                    data.toggle();
                }
            });
        });
    });

    /**
     * --------------------------------------------
     * @file AdminLTE treeview.ts
     * @description Treeview plugin for AdminLTE.
     * @license MIT
     * --------------------------------------------
     */
    /**
     * ------------------------------------------------------------------------
     * Constants
     * ------------------------------------------------------------------------
     */
    // const NAME = 'Treeview'
    const DATA_KEY$4 = 'lte.treeview';
    const EVENT_KEY$4 = `.${DATA_KEY$4}`;
    const EVENT_EXPANDED$3 = `expanded${EVENT_KEY$4}`;
    const EVENT_COLLAPSED$3 = `collapsed${EVENT_KEY$4}`;
    // const EVENT_LOAD_DATA_API = `load${EVENT_KEY}`
    const CLASS_NAME_MENU_OPEN = 'menu-open';
    const SELECTOR_NAV_ITEM = '.nav-item';
    const SELECTOR_NAV_LINK$1 = '.nav-link';
    const SELECTOR_TREEVIEW_MENU = '.nav-treeview';
    const SELECTOR_DATA_TOGGLE$2 = '[data-lte-toggle="treeview"]';
    const Default$2 = {
        animationSpeed: 300,
        accordion: true
    };
    /**
     * Class Definition
     * ====================================================
     */
    class Treeview {
        constructor(element, config) {
            this._element = element;
            this._config = Object.assign(Object.assign({}, Default$2), config);
        }
        open() {
            var _a, _b;
            const event = new Event(EVENT_EXPANDED$3);
            if (this._config.accordion) {
                const openMenuList = (_a = this._element.parentElement) === null || _a === void 0 ? void 0 : _a.querySelectorAll(`${SELECTOR_NAV_ITEM}.${CLASS_NAME_MENU_OPEN}`);
                openMenuList === null || openMenuList === void 0 ? void 0 : openMenuList.forEach(openMenu => {
                    if (openMenu !== this._element.parentElement) {
                        openMenu.classList.remove(CLASS_NAME_MENU_OPEN);
                        const childElement = openMenu === null || openMenu === void 0 ? void 0 : openMenu.querySelector(SELECTOR_TREEVIEW_MENU);
                        if (childElement) {
                            slideUp(childElement, this._config.animationSpeed);
                        }
                    }
                });
            }
            this._element.classList.add(CLASS_NAME_MENU_OPEN);
            const childElement = (_b = this._element) === null || _b === void 0 ? void 0 : _b.querySelector(SELECTOR_TREEVIEW_MENU);
            if (childElement) {
                slideDown(childElement, this._config.animationSpeed);
            }
            this._element.dispatchEvent(event);
        }
        close() {
            var _a;
            const event = new Event(EVENT_COLLAPSED$3);
            this._element.classList.remove(CLASS_NAME_MENU_OPEN);
            const childElement = (_a = this._element) === null || _a === void 0 ? void 0 : _a.querySelector(SELECTOR_TREEVIEW_MENU);
            if (childElement) {
                slideUp(childElement, this._config.animationSpeed);
            }
            this._element.dispatchEvent(event);
        }
        toggle() {
            if (this._element.classList.contains(CLASS_NAME_MENU_OPEN)) {
                this.close();
            }
            else {
                this.open();
            }
        }
    }
    /**
     * ------------------------------------------------------------------------
     * Data Api implementation
     * ------------------------------------------------------------------------
     */
    onDOMContentLoaded(() => {
        const button = document.querySelectorAll(SELECTOR_DATA_TOGGLE$2);
        button.forEach(btn => {
            btn.addEventListener('click', event => {
                const target = event.target;
                const targetItem = target.closest(SELECTOR_NAV_ITEM);
                const targetLink = target.closest(SELECTOR_NAV_LINK$1);
                if ((target === null || target === void 0 ? void 0 : target.getAttribute('href')) === '#' || (targetLink === null || targetLink === void 0 ? void 0 : targetLink.getAttribute('href')) === '#') {
                    event.preventDefault();
                }
                if (targetItem) {
                    const data = new Treeview(targetItem, Default$2);
                    data.toggle();
                }
            });
        });
    });

    /**
     * --------------------------------------------
     * @file AdminLTE direct-chat.ts
     * @description Direct chat for AdminLTE.
     * @license MIT
     * --------------------------------------------
     */
    /**
     * Constants
     * ====================================================
     */
    const DATA_KEY$3 = 'lte.direct-chat';
    const EVENT_KEY$3 = `.${DATA_KEY$3}`;
    const EVENT_EXPANDED$2 = `expanded${EVENT_KEY$3}`;
    const EVENT_COLLAPSED$2 = `collapsed${EVENT_KEY$3}`;
    const SELECTOR_DATA_TOGGLE$1 = '[data-lte-toggle="chat-pane"]';
    const SELECTOR_DIRECT_CHAT = '.direct-chat';
    const CLASS_NAME_DIRECT_CHAT_OPEN = 'direct-chat-contacts-open';
    /**
     * Class Definition
     * ====================================================
     */
    class DirectChat {
        constructor(element) {
            this._element = element;
        }
        toggle() {
            if (this._element.classList.contains(CLASS_NAME_DIRECT_CHAT_OPEN)) {
                const event = new Event(EVENT_COLLAPSED$2);
                this._element.classList.remove(CLASS_NAME_DIRECT_CHAT_OPEN);
                this._element.dispatchEvent(event);
            }
            else {
                const event = new Event(EVENT_EXPANDED$2);
                this._element.classList.add(CLASS_NAME_DIRECT_CHAT_OPEN);
                this._element.dispatchEvent(event);
            }
        }
    }
    /**
     *
     * Data Api implementation
     * ====================================================
     */
    onDOMContentLoaded(() => {
        const button = document.querySelectorAll(SELECTOR_DATA_TOGGLE$1);
        button.forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                const target = event.target;
                const chatPane = target.closest(SELECTOR_DIRECT_CHAT);
                if (chatPane) {
                    const data = new DirectChat(chatPane);
                    data.toggle();
                }
            });
        });
    });

    /**
     * --------------------------------------------
     * @file AdminLTE card-widget.ts
     * @description Card widget for AdminLTE.
     * @license MIT
     * --------------------------------------------
     */
    /**
     * Constants
     * ====================================================
     */
    const DATA_KEY$2 = 'lte.card-widget';
    const EVENT_KEY$2 = `.${DATA_KEY$2}`;
    const EVENT_COLLAPSED$1 = `collapsed${EVENT_KEY$2}`;
    const EVENT_EXPANDED$1 = `expanded${EVENT_KEY$2}`;
    const EVENT_REMOVE = `remove${EVENT_KEY$2}`;
    const EVENT_MAXIMIZED$1 = `maximized${EVENT_KEY$2}`;
    const EVENT_MINIMIZED$1 = `minimized${EVENT_KEY$2}`;
    const CLASS_NAME_CARD = 'card';
    const CLASS_NAME_COLLAPSED = 'collapsed-card';
    const CLASS_NAME_COLLAPSING = 'collapsing-card';
    const CLASS_NAME_EXPANDING = 'expanding-card';
    const CLASS_NAME_WAS_COLLAPSED = 'was-collapsed';
    const CLASS_NAME_MAXIMIZED = 'maximized-card';
    const SELECTOR_DATA_REMOVE = '[data-lte-toggle="card-remove"]';
    const SELECTOR_DATA_COLLAPSE = '[data-lte-toggle="card-collapse"]';
    const SELECTOR_DATA_MAXIMIZE = '[data-lte-toggle="card-maximize"]';
    const SELECTOR_CARD = `.${CLASS_NAME_CARD}`;
    const SELECTOR_CARD_BODY = '.card-body';
    const SELECTOR_CARD_FOOTER = '.card-footer';
    const Default$1 = {
        animationSpeed: 500,
        collapseTrigger: SELECTOR_DATA_COLLAPSE,
        removeTrigger: SELECTOR_DATA_REMOVE,
        maximizeTrigger: SELECTOR_DATA_MAXIMIZE
    };
    class CardWidget {
        constructor(element, config) {
            this._element = element;
            this._parent = element.closest(SELECTOR_CARD);
            if (element.classList.contains(CLASS_NAME_CARD)) {
                this._parent = element;
            }
            this._config = Object.assign(Object.assign({}, Default$1), config);
        }
        collapse() {
            var _a, _b;
            const event = new Event(EVENT_COLLAPSED$1);
            if (this._parent) {
                this._parent.classList.add(CLASS_NAME_COLLAPSING);
                const elm = (_a = this._parent) === null || _a === void 0 ? void 0 : _a.querySelectorAll(`${SELECTOR_CARD_BODY}, ${SELECTOR_CARD_FOOTER}`);
                elm.forEach(el => {
                    if (el instanceof HTMLElement) {
                        slideUp(el, this._config.animationSpeed);
                    }
                });
                setTimeout(() => {
                    if (this._parent) {
                        this._parent.classList.add(CLASS_NAME_COLLAPSED);
                        this._parent.classList.remove(CLASS_NAME_COLLAPSING);
                    }
                }, this._config.animationSpeed);
            }
            (_b = this._element) === null || _b === void 0 ? void 0 : _b.dispatchEvent(event);
        }
        expand() {
            var _a, _b;
            const event = new Event(EVENT_EXPANDED$1);
            if (this._parent) {
                this._parent.classList.add(CLASS_NAME_EXPANDING);
                const elm = (_a = this._parent) === null || _a === void 0 ? void 0 : _a.querySelectorAll(`${SELECTOR_CARD_BODY}, ${SELECTOR_CARD_FOOTER}`);
                elm.forEach(el => {
                    if (el instanceof HTMLElement) {
                        slideDown(el, this._config.animationSpeed);
                    }
                });
                setTimeout(() => {
                    if (this._parent) {
                        this._parent.classList.remove(CLASS_NAME_COLLAPSED);
                        this._parent.classList.remove(CLASS_NAME_EXPANDING);
                    }
                }, this._config.animationSpeed);
            }
            (_b = this._element) === null || _b === void 0 ? void 0 : _b.dispatchEvent(event);
        }
        remove() {
            var _a;
            const event = new Event(EVENT_REMOVE);
            if (this._parent) {
                slideUp(this._parent, this._config.animationSpeed);
            }
            (_a = this._element) === null || _a === void 0 ? void 0 : _a.dispatchEvent(event);
        }
        toggle() {
            var _a;
            if ((_a = this._parent) === null || _a === void 0 ? void 0 : _a.classList.contains(CLASS_NAME_COLLAPSED)) {
                this.expand();
                return;
            }
            this.collapse();
        }
        maximize() {
            var _a;
            const event = new Event(EVENT_MAXIMIZED$1);
            if (this._parent) {
                this._parent.style.height = `${this._parent.offsetHeight}px`;
                this._parent.style.width = `${this._parent.offsetWidth}px`;
                this._parent.style.transition = 'all .15s';
                setTimeout(() => {
                    const htmlTag = document.querySelector('html');
                    if (htmlTag) {
                        htmlTag.classList.add(CLASS_NAME_MAXIMIZED);
                    }
                    if (this._parent) {
                        this._parent.classList.add(CLASS_NAME_MAXIMIZED);
                        if (this._parent.classList.contains(CLASS_NAME_COLLAPSED)) {
                            this._parent.classList.add(CLASS_NAME_WAS_COLLAPSED);
                        }
                    }
                }, 150);
            }
            (_a = this._element) === null || _a === void 0 ? void 0 : _a.dispatchEvent(event);
        }
        minimize() {
            var _a;
            const event = new Event(EVENT_MINIMIZED$1);
            if (this._parent) {
                this._parent.style.height = 'auto';
                this._parent.style.width = 'auto';
                this._parent.style.transition = 'all .15s';
                setTimeout(() => {
                    var _a;
                    const htmlTag = document.querySelector('html');
                    if (htmlTag) {
                        htmlTag.classList.remove(CLASS_NAME_MAXIMIZED);
                    }
                    if (this._parent) {
                        this._parent.classList.remove(CLASS_NAME_MAXIMIZED);
                        if ((_a = this._parent) === null || _a === void 0 ? void 0 : _a.classList.contains(CLASS_NAME_WAS_COLLAPSED)) {
                            this._parent.classList.remove(CLASS_NAME_WAS_COLLAPSED);
                        }
                    }
                }, 10);
            }
            (_a = this._element) === null || _a === void 0 ? void 0 : _a.dispatchEvent(event);
        }
        toggleMaximize() {
            var _a;
            if ((_a = this._parent) === null || _a === void 0 ? void 0 : _a.classList.contains(CLASS_NAME_MAXIMIZED)) {
                this.minimize();
                return;
            }
            this.maximize();
        }
    }
    /**
     *
     * Data Api implementation
     * ====================================================
     */
    onDOMContentLoaded(() => {
        const collapseBtn = document.querySelectorAll(SELECTOR_DATA_COLLAPSE);
        collapseBtn.forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                const target = event.target;
                const data = new CardWidget(target, Default$1);
                data.toggle();
            });
        });
        const removeBtn = document.querySelectorAll(SELECTOR_DATA_REMOVE);
        removeBtn.forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                const target = event.target;
                const data = new CardWidget(target, Default$1);
                data.remove();
            });
        });
        const maxBtn = document.querySelectorAll(SELECTOR_DATA_MAXIMIZE);
        maxBtn.forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                const target = event.target;
                const data = new CardWidget(target, Default$1);
                data.toggleMaximize();
            });
        });
    });

    /**
     * --------------------------------------------
     * @file AdminLTE fullscreen.ts
     * @description Fullscreen plugin for AdminLTE.
     * @license MIT
     * --------------------------------------------
     */
    /**
     * Constants
     * ============================================================================
     */
    const DATA_KEY$1 = 'lte.fullscreen';
    const EVENT_KEY$1 = `.${DATA_KEY$1}`;
    const EVENT_MAXIMIZED = `maximized${EVENT_KEY$1}`;
    const EVENT_MINIMIZED = `minimized${EVENT_KEY$1}`;
    const SELECTOR_FULLSCREEN_TOGGLE = '[data-lte-toggle="fullscreen"]';
    const SELECTOR_MAXIMIZE_ICON = '[data-lte-icon="maximize"]';
    const SELECTOR_MINIMIZE_ICON = '[data-lte-icon="minimize"]';
    /**
     * Class Definition.
     * ============================================================================
     */
    class FullScreen {
        constructor(element, config) {
            this._element = element;
            this._config = config;
        }
        inFullScreen() {
            const event = new Event(EVENT_MAXIMIZED);
            const iconMaximize = document.querySelector(SELECTOR_MAXIMIZE_ICON);
            const iconMinimize = document.querySelector(SELECTOR_MINIMIZE_ICON);
            void document.documentElement.requestFullscreen();
            if (iconMaximize) {
                iconMaximize.style.display = 'none';
            }
            if (iconMinimize) {
                iconMinimize.style.display = 'block';
            }
            this._element.dispatchEvent(event);
        }
        outFullscreen() {
            const event = new Event(EVENT_MINIMIZED);
            const iconMaximize = document.querySelector(SELECTOR_MAXIMIZE_ICON);
            const iconMinimize = document.querySelector(SELECTOR_MINIMIZE_ICON);
            void document.exitFullscreen();
            if (iconMaximize) {
                iconMaximize.style.display = 'block';
            }
            if (iconMinimize) {
                iconMinimize.style.display = 'none';
            }
            this._element.dispatchEvent(event);
        }
        toggleFullScreen() {
            if (document.fullscreenEnabled) {
                if (document.fullscreenElement) {
                    this.outFullscreen();
                }
                else {
                    this.inFullScreen();
                }
            }
        }
    }
    /**
     * Data Api implementation
     * ============================================================================
     */
    onDOMContentLoaded(() => {
        const buttons = document.querySelectorAll(SELECTOR_FULLSCREEN_TOGGLE);
        buttons.forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                const target = event.target;
                const button = target.closest(SELECTOR_FULLSCREEN_TOGGLE);
                if (button) {
                    const data = new FullScreen(button, undefined);
                    data.toggleFullScreen();
                }
            });
        });
    });

    /**
     * --------------------------------------------
     * AdminLTE sidebar-search.ts
     * License MIT
     * --------------------------------------------
     */
    /**
     * Constants
     * ====================================================
     */
    // const NAME = 'SidebarSearch'
    // const DATA_KEY = 'lte.sidebar-search'
    const CLASS_NAME_OPEN = 'sidebar-search-open';
    const CLASS_NAME_ICON_SEARCH = 'bi-search';
    const CLASS_NAME_ICON_CLOSE = 'bi-x-lg';
    const CLASS_NAME_HEADER = 'nav-header';
    const CLASS_NAME_SEARCH_RESULTS = 'sidebar-search-results';
    const CLASS_NAME_LIST_GROUP = 'list-group';
    const SELECTOR_DATA_WIDGET = '[data-widget="sidebar-search"]';
    const SELECTOR_SIDEBAR = '.app-sidebar .sidebar-menu';
    const SELECTOR_NAV_LINK = '.nav-link';
    const SELECTOR_NAV_TREEVIEW = '.nav-treeview';
    const SELECTOR_SEARCH_INPUT = `${SELECTOR_DATA_WIDGET} .form-control`;
    const SELECTOR_SEARCH_BUTTON = `${SELECTOR_DATA_WIDGET} .btn`;
    const SELECTOR_SEARCH_ICON = `${SELECTOR_SEARCH_BUTTON} i`;
    const SELECTOR_SEARCH_LIST_GROUP = `.${CLASS_NAME_LIST_GROUP}`;
    const SELECTOR_SEARCH_RESULTS = `.${CLASS_NAME_SEARCH_RESULTS}`;
    const SELECTOR_SEARCH_RESULTS_GROUP = `${SELECTOR_SEARCH_RESULTS} .${CLASS_NAME_LIST_GROUP}`;
    const Defaults = {
        arrowSign: '->',
        minLength: 3,
        maxResults: 7,
        highlightName: true,
        highlightPath: false,
        highlightClass: 'text-light',
        notFoundText: 'No element found!'
    };
    const SearchItems = [];
    /**
     * Class Definition
     * ====================================================
     */
    class SidebarSearch {
        constructor(_element, _options) {
            this.items = [];
            this.element = _element;
            this.options = Object.assign(Object.assign({}, Defaults), _options);
            this.items = [];
        }
        // Public
        init() {
            var _a, _b, _c;
            if (document.querySelectorAll(SELECTOR_DATA_WIDGET).length === 0) {
                return;
            }
            if (document.querySelectorAll(`${SELECTOR_DATA_WIDGET} + ${SELECTOR_SEARCH_RESULTS}`).length === 0) {
                (_a = document.querySelector(SELECTOR_DATA_WIDGET)) === null || _a === void 0 ? void 0 : _a.insertAdjacentHTML('afterend', `<div class="${CLASS_NAME_SEARCH_RESULTS}"></div>`);
            }
            if (document.querySelectorAll(`${SELECTOR_SEARCH_RESULTS} ${SELECTOR_SEARCH_LIST_GROUP}`).length === 0) {
                // append
                (_b = document.querySelector(SELECTOR_SEARCH_RESULTS)) === null || _b === void 0 ? void 0 : _b.insertAdjacentHTML('beforeend', `<div class="${CLASS_NAME_LIST_GROUP}"></div>`);
            }
            this._addNotFound();
            const childs = (_c = document.querySelector(SELECTOR_SIDEBAR)) === null || _c === void 0 ? void 0 : _c.children;
            if (childs) {
                Array.from(childs).forEach(child => {
                    this._parseItem(child);
                });
            }
        }
        search() {
            const searchElement = document.querySelector(SELECTOR_SEARCH_INPUT);
            const searchValue = (searchElement) ? (searchElement.value.toLowerCase()) : '';
            const searchResultGroup = document.querySelector(SELECTOR_SEARCH_RESULTS_GROUP);
            if (searchValue.length < this.options.minLength) {
                searchResultGroup.innerHTML = '';
                this._addNotFound();
                this.close();
                return;
            }
            const searchResults = SearchItems.filter(item => (item.name).toLowerCase().includes(searchValue));
            const endResults = searchResults.slice(0, this.options.maxResults);
            searchResultGroup.innerHTML = '';
            if (endResults.length === 0) {
                this._addNotFound();
            }
            else {
                endResults.forEach(result => {
                    // append
                    searchResultGroup.insertAdjacentHTML('beforeend', this._renderItem(escape(result.name), encodeURI(result.link), [result.path]));
                });
            }
            this.open();
        }
        open() {
            var _a, _b, _c, _d;
            (_b = (_a = document.querySelector(SELECTOR_DATA_WIDGET)) === null || _a === void 0 ? void 0 : _a.parentElement) === null || _b === void 0 ? void 0 : _b.classList.add(CLASS_NAME_OPEN);
            (_c = document.querySelector(SELECTOR_SEARCH_ICON)) === null || _c === void 0 ? void 0 : _c.classList.remove(CLASS_NAME_ICON_SEARCH);
            (_d = document.querySelector(SELECTOR_SEARCH_ICON)) === null || _d === void 0 ? void 0 : _d.classList.add(CLASS_NAME_ICON_CLOSE);
        }
        close() {
            var _a, _b, _c, _d;
            (_b = (_a = document.querySelector(SELECTOR_DATA_WIDGET)) === null || _a === void 0 ? void 0 : _a.parentElement) === null || _b === void 0 ? void 0 : _b.classList.remove(CLASS_NAME_OPEN);
            (_c = document.querySelector(SELECTOR_SEARCH_ICON)) === null || _c === void 0 ? void 0 : _c.classList.remove(CLASS_NAME_ICON_CLOSE);
            (_d = document.querySelector(SELECTOR_SEARCH_ICON)) === null || _d === void 0 ? void 0 : _d.classList.add(CLASS_NAME_ICON_SEARCH);
        }
        toggle() {
            var _a, _b;
            if ((_b = (_a = document.querySelector(SELECTOR_DATA_WIDGET)) === null || _a === void 0 ? void 0 : _a.parentElement) === null || _b === void 0 ? void 0 : _b.classList.contains(CLASS_NAME_OPEN)) {
                this.close();
            }
            else {
                this.open();
            }
        }
        // Private
        _parseItem(item, path = []) {
            var _a, _b, _c;
            if (item.classList.contains(CLASS_NAME_HEADER)) {
                return;
            }
            const itemObject = {
                name: '',
                link: '',
                path: ''
            };
            const navLink = item.cloneNode(true).querySelector(`${SELECTOR_NAV_LINK}`);
            const navTreeview = item.cloneNode(true).querySelector(`${SELECTOR_NAV_TREEVIEW}`);
            const link = (_a = (navLink === null || navLink === void 0 ? void 0 : navLink.getAttribute('href'))) !== null && _a !== void 0 ? _a : '';
            const name = (_c = (_b = navLink === null || navLink === void 0 ? void 0 : navLink.querySelector('p')) === null || _b === void 0 ? void 0 : _b.textContent) !== null && _c !== void 0 ? _c : '';
            itemObject.name = this._trimText(name);
            itemObject.link = link;
            itemObject.path = path.join(` ${this.options.arrowSign} `);
            if (navTreeview === null) {
                SearchItems.push(itemObject);
            }
            else {
                const newPath = itemObject.path.concat(itemObject.name);
                const childs = navTreeview.children;
                // navTreeview.children().each((i, child) => {
                //   this._parseItem(child, newPath)
                // })
                if (childs) {
                    Array.from(childs).forEach(child => {
                        this._parseItem(child, [newPath]);
                    });
                }
            }
        }
        //
        _trimText(text) {
            return (text.replace(/(\r\n|\n|\r)/gm, ' ')).trim();
        }
        _renderItem(name, link, path) {
            let varpath = path.join(` ${this.options.arrowSign} `);
            let varname = unescape(name);
            const varlink = decodeURI(link);
            if (this.options.highlightName || this.options.highlightPath) {
                const searchElement = document.querySelector(SELECTOR_SEARCH_INPUT);
                const searchValue = (searchElement) ? (searchElement.value.toLowerCase()) : '';
                const regExp = new RegExp(searchValue, 'gi');
                if (this.options.highlightName) {
                    varname = varname.replace(regExp, str => {
                        return `<strong class="${this.options.highlightClass}">${str}</strong>`;
                    });
                }
                if (this.options.highlightPath) {
                    varpath = varpath.replace(regExp, str => {
                        return `<strong class="${this.options.highlightClass}">${str}</strong>`;
                    });
                }
            }
            const groupItemElement = `<a class="list-group-item" href="${decodeURIComponent(varlink)}"><div class="search-title">${varname}</div><div class="search-path">${varpath}</div></a>`;
            // const groupItemElement = $('<a/>', {
            //   href: decodeURIComponent(varlink),
            //   class: 'list-group-item'
            // })
            // const searchTitleElement = $('<div/>', {
            //   class: 'search-title'
            // }).html(varname)
            // const searchPathElement = $('<div/>', {
            //   class: 'search-path'
            // }).html(varpath)
            // groupItemElement.append(searchTitleElement).append(searchPathElement)
            return groupItemElement;
        }
        _addNotFound() {
            var _a;
            // append
            (_a = document.querySelector(SELECTOR_SEARCH_RESULTS_GROUP)) === null || _a === void 0 ? void 0 : _a.insertAdjacentHTML('beforeend', this._renderItem(this.options.notFoundText, '#', []));
        }
    }
    //
    // /**
    //  * Data API
    //  * ====================================================
    //  */
    // $(document).on('click', SELECTOR_SEARCH_BUTTON, event => {
    //   event.preventDefault()
    //
    //   SidebarSearch._jQueryInterface.call($(SELECTOR_DATA_WIDGET), 'toggle')
    // })
    //
    // $(document).on('keyup', SELECTOR_SEARCH_INPUT, event => {
    //   if (event.keyCode == 38) {
    //     event.preventDefault()
    //     $(SELECTOR_SEARCH_RESULTS_GROUP).children().last().focus()
    //     return
    //   }
    //
    //   if (event.keyCode == 40) {
    //     event.preventDefault()
    //     $(SELECTOR_SEARCH_RESULTS_GROUP).children().first().focus()
    //     return
    //   }
    //
    //   setTimeout(() => {
    //     SidebarSearch._jQueryInterface.call($(SELECTOR_DATA_WIDGET), 'search')
    //   }, 100)
    // })
    //
    // $(document).on('keydown', SELECTOR_SEARCH_RESULTS_GROUP, event => {
    //   const $focused = $(':focus')
    //
    //   if (event.keyCode == 38) {
    //     event.preventDefault()
    //
    //     if ($focused.is(':first-child')) {
    //       $focused.siblings().last().focus()
    //     } else {
    //       $focused.prev().focus()
    //     }
    //   }
    //
    //   if (event.keyCode == 40) {
    //     event.preventDefault()
    //
    //     if ($focused.is(':last-child')) {
    //       $focused.siblings().first().focus()
    //     } else {
    //       $focused.next().focus()
    //     }
    //   }
    // })
    //
    // $(window).on('load', () => {
    //   SidebarSearch._jQueryInterface.call($(SELECTOR_DATA_WIDGET), 'init')
    // })
    //
    // /**
    //  * jQuery API
    //  * ====================================================
    //  */
    //
    // $.fn[NAME] = SidebarSearch._jQueryInterface
    // $.fn[NAME].Constructor = SidebarSearch
    // $.fn[NAME].noConflict = function () {
    //   $.fn[NAME] = JQUERY_NO_CONFLICT
    //   return SidebarSearch._jQueryInterface
    // }
    onDOMContentLoaded(() => {
        const sidebarSearchInput = document.querySelector('[data-widget="sidebar-search"] .form-control');
        const sidebarSearchElemnt = document === null || document === void 0 ? void 0 : document.querySelector(SELECTOR_DATA_WIDGET);
        const sidebarSearch = (sidebarSearchElemnt) ? new SidebarSearch(sidebarSearchElemnt, Defaults) : null;
        sidebarSearch === null || sidebarSearch === void 0 ? void 0 : sidebarSearch.init();
        const searchButton = document.querySelector(SELECTOR_SEARCH_BUTTON);
        const searchResultGroup = document.querySelector(SELECTOR_SEARCH_RESULTS_GROUP);
        const searchResultGroupChilds = searchResultGroup === null || searchResultGroup === void 0 ? void 0 : searchResultGroup.children;
        searchButton === null || searchButton === void 0 ? void 0 : searchButton.addEventListener('click', event => {
            event.preventDefault();
            sidebarSearch === null || sidebarSearch === void 0 ? void 0 : sidebarSearch.toggle();
        });
        sidebarSearchInput === null || sidebarSearchInput === void 0 ? void 0 : sidebarSearchInput.addEventListener('keyup', e => {
            const event = e;
            sidebarSearch === null || sidebarSearch === void 0 ? void 0 : sidebarSearch.toggle();
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (searchResultGroupChilds) {
                    const lastchild = searchResultGroupChilds.item(searchResultGroupChilds.length - 1);
                    lastchild.focus();
                }
            }
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (searchResultGroupChilds) {
                    const firtchild = searchResultGroupChilds.item(0);
                    firtchild.focus();
                }
            }
            setTimeout(() => {
                sidebarSearch === null || sidebarSearch === void 0 ? void 0 : sidebarSearch.search();
            }, 100);
        });
        searchResultGroup === null || searchResultGroup === void 0 ? void 0 : searchResultGroup.addEventListener('keydown', e => {
            var _a, _b;
            const event = e;
            const focused = document.querySelector(':focus');
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (focused) {
                    if (focused === null || focused === void 0 ? void 0 : focused.matches(':first-child')) {
                        const lastChild = (_a = focused === null || focused === void 0 ? void 0 : focused.parentNode) === null || _a === void 0 ? void 0 : _a.lastChild;
                        if (lastChild) {
                            lastChild.focus();
                        }
                    }
                    else {
                        const previousSibling = focused.previousSibling;
                        if (previousSibling) {
                            previousSibling.focus();
                        }
                    }
                }
            }
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (focused) {
                    if (focused === null || focused === void 0 ? void 0 : focused.matches(':last-child')) {
                        // $focused.siblings().first().focus()
                        const firstChild = (_b = focused === null || focused === void 0 ? void 0 : focused.parentNode) === null || _b === void 0 ? void 0 : _b.firstChild;
                        if (firstChild) {
                            firstChild.focus();
                        }
                    }
                    else {
                        // $focused.next().focus()
                        const nextSibling = focused.nextSibling;
                        if (nextSibling) {
                            nextSibling.focus();
                        }
                    }
                }
            }
        });
    });

    /**
     * --------------------------------------------
     * AdminLTE ControlSidebar.js
     * License MIT
     * --------------------------------------------
     */


    /**
     * Constants
     * ====================================================
     */

    const NAME = 'ControlSidebar';
    const DATA_KEY = 'lte.controlsidebar';
    const EVENT_KEY = `.${DATA_KEY}`;
    const JQUERY_NO_CONFLICT = jQuery.fn[NAME];

    const EVENT_COLLAPSED = `collapsed${EVENT_KEY}`;
    const EVENT_COLLAPSED_DONE = `collapsed-done${EVENT_KEY}`;
    const EVENT_EXPANDED = `expanded${EVENT_KEY}`;

    const SELECTOR_CONTROL_SIDEBAR = '.control-sidebar';
    const SELECTOR_CONTROL_SIDEBAR_CONTENT = '.control-sidebar-content';
    const SELECTOR_DATA_TOGGLE = '[data-widget="control-sidebar"]';
    const SELECTOR_HEADER = '.app-header';
    const SELECTOR_FOOTER = '.app-footer';

    const CLASS_NAME_CONTROL_SIDEBAR_ANIMATE = 'control-sidebar-animate';
    const CLASS_NAME_CONTROL_SIDEBAR_OPEN = 'control-sidebar-open';
    const CLASS_NAME_CONTROL_SIDEBAR_SLIDE = 'control-sidebar-slide-open';
    const CLASS_NAME_LAYOUT_FIXED = 'layout-fixed';
    const CLASS_NAME_NAVBAR_FIXED = 'layout-navbar-fixed';
    const CLASS_NAME_NAVBAR_SM_FIXED = 'layout-sm-navbar-fixed';
    const CLASS_NAME_NAVBAR_MD_FIXED = 'layout-md-navbar-fixed';
    const CLASS_NAME_NAVBAR_LG_FIXED = 'layout-lg-navbar-fixed';
    const CLASS_NAME_NAVBAR_XL_FIXED = 'layout-xl-navbar-fixed';
    const CLASS_NAME_FOOTER_FIXED = 'layout-footer-fixed';
    const CLASS_NAME_FOOTER_SM_FIXED = 'layout-sm-footer-fixed';
    const CLASS_NAME_FOOTER_MD_FIXED = 'layout-md-footer-fixed';
    const CLASS_NAME_FOOTER_LG_FIXED = 'layout-lg-footer-fixed';
    const CLASS_NAME_FOOTER_XL_FIXED = 'layout-xl-footer-fixed';

    const Default = {
      controlsidebarSlide: true,
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'l',
      target: SELECTOR_CONTROL_SIDEBAR,
      animationSpeed: 300
    };

    /**
     * Class Definition
     * ====================================================
     */

    class ControlSidebar {
      constructor(element, config) {
        this._element = element;
        this._config = config;
      }

      // Public

      collapse() {
        const $body = jQuery('body');
        const $html = jQuery('html');

        // Show the control sidebar
        if (this._config.controlsidebarSlide) {
          $html.addClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
          $body.removeClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE).delay(300).queue(function () {
            jQuery(SELECTOR_CONTROL_SIDEBAR).hide();
            $html.removeClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
            jQuery(this).dequeue();
          });
        } else {
          $body.removeClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN);
        }

        jQuery(this._element).trigger(jQuery.Event(EVENT_COLLAPSED));

        setTimeout(() => {
          jQuery(this._element).trigger(jQuery.Event(EVENT_COLLAPSED_DONE));
        }, this._config.animationSpeed);
      }

      show(toggle = false) {
        const $body = jQuery('body');
        const $html = jQuery('html');

        if (toggle) {
          jQuery(SELECTOR_CONTROL_SIDEBAR).hide();
        }

        // Collapse the control sidebar
        if (this._config.controlsidebarSlide) {
          $html.addClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
          jQuery(this._config.target).show().delay(10).queue(function () {
            $body.addClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE).delay(300).queue(function () {
              $html.removeClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
              jQuery(this).dequeue();
            });
            jQuery(this).dequeue();
          });
        } else {
          $body.addClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN);
        }

        this._fixHeight();
        this._fixScrollHeight();

        jQuery(this._element).trigger(jQuery.Event(EVENT_EXPANDED));
      }

      toggle() {
        const $body = jQuery('body');
        const { target } = this._config;

        const notVisible = !jQuery(target).is(':visible');
        const shouldClose = ($body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN) ||
          $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE));
        const shouldToggle = notVisible && ($body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN) ||
          $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE));

        if (notVisible || shouldToggle) {
          // Open the control sidebar
          this.show(notVisible);
        } else if (shouldClose) {
          // Close the control sidebar
          this.collapse();
        }
      }

      // Private

      _init() {
        const $body = jQuery('body');
        const shouldNotHideAll = $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN) ||
          $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE);

        if (shouldNotHideAll) {
          jQuery(SELECTOR_CONTROL_SIDEBAR).not(this._config.target).hide();
          jQuery(this._config.target).css('display', 'block');
        } else {
          jQuery(SELECTOR_CONTROL_SIDEBAR).hide();
        }

        this._fixHeight();
        this._fixScrollHeight();

        jQuery(window).resize(() => {
          this._fixHeight();
          this._fixScrollHeight();
        });

        jQuery(window).scroll(() => {
          const $body = jQuery('body');
          const shouldFixHeight = $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN) ||
            $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE);

          if (shouldFixHeight) {
            this._fixScrollHeight();
          }
        });
      }

      _isNavbarFixed() {
        const $body = jQuery('body');
        return (
          $body.hasClass(CLASS_NAME_NAVBAR_FIXED) ||
          $body.hasClass(CLASS_NAME_NAVBAR_SM_FIXED) ||
          $body.hasClass(CLASS_NAME_NAVBAR_MD_FIXED) ||
          $body.hasClass(CLASS_NAME_NAVBAR_LG_FIXED) ||
          $body.hasClass(CLASS_NAME_NAVBAR_XL_FIXED)
        )
      }

      _isFooterFixed() {
        const $body = jQuery('body');
        return (
          $body.hasClass(CLASS_NAME_FOOTER_FIXED) ||
          $body.hasClass(CLASS_NAME_FOOTER_SM_FIXED) ||
          $body.hasClass(CLASS_NAME_FOOTER_MD_FIXED) ||
          $body.hasClass(CLASS_NAME_FOOTER_LG_FIXED) ||
          $body.hasClass(CLASS_NAME_FOOTER_XL_FIXED)
        )
      }

      _fixScrollHeight() {
        const $body = jQuery('body');
        const $controlSidebar = jQuery(this._config.target);

        if (!$body.hasClass(CLASS_NAME_LAYOUT_FIXED)) {
          return
        }

        const heights = {
          scroll: jQuery(document).height(),
          window: jQuery(window).height(),
          header: jQuery(SELECTOR_HEADER).outerHeight(),
          footer: jQuery(SELECTOR_FOOTER).outerHeight()
        };
        const positions = {
          bottom: Math.abs((heights.window + jQuery(window).scrollTop()) - heights.scroll),
          top: jQuery(window).scrollTop()
        };

        const navbarFixed = this._isNavbarFixed() && jQuery(SELECTOR_HEADER).css('position') === 'fixed';

        const footerFixed = this._isFooterFixed() && jQuery(SELECTOR_FOOTER).css('position') === 'fixed';

        const $controlsidebarContent = jQuery(`${this._config.target}, ${this._config.target} ${SELECTOR_CONTROL_SIDEBAR_CONTENT}`);

        if (positions.top === 0 && positions.bottom === 0) {
          $controlSidebar.css({
            bottom: heights.footer,
            top: heights.header
          });
          $controlsidebarContent.css('height', heights.window - (heights.header + heights.footer));
        } else if (positions.bottom <= heights.footer) {
          if (footerFixed === false) {
            const top = heights.header - positions.top;
            $controlSidebar.css('bottom', heights.footer - positions.bottom).css('top', top >= 0 ? top : 0);
            $controlsidebarContent.css('height', heights.window - (heights.footer - positions.bottom));
          } else {
            $controlSidebar.css('bottom', heights.footer);
          }
        } else if (positions.top <= heights.header) {
          if (navbarFixed === false) {
            $controlSidebar.css('top', heights.header - positions.top);
            $controlsidebarContent.css('height', heights.window - (heights.header - positions.top));
          } else {
            $controlSidebar.css('top', heights.header);
          }
        } else if (navbarFixed === false) {
          $controlSidebar.css('top', 0);
          $controlsidebarContent.css('height', heights.window);
        } else {
          $controlSidebar.css('top', heights.header);
        }

        if (footerFixed && navbarFixed) {
          $controlsidebarContent.css('height', '100%');
          $controlSidebar.css('height', '');
        } else if (footerFixed || navbarFixed) {
          $controlsidebarContent.css('height', '100%');
          $controlsidebarContent.css('height', '');
        }
      }

      _fixHeight() {
        const $body = jQuery('body');
        const $controlSidebar = jQuery(`${this._config.target} ${SELECTOR_CONTROL_SIDEBAR_CONTENT}`);

        if (!$body.hasClass(CLASS_NAME_LAYOUT_FIXED)) {
          $controlSidebar.attr('style', '');
          return
        }

        const heights = {
          window: jQuery(window).height(),
          header: jQuery(SELECTOR_HEADER).outerHeight(),
          footer: jQuery(SELECTOR_FOOTER).outerHeight()
        };

        let sidebarHeight = heights.window - heights.header;

        if (this._isFooterFixed() && jQuery(SELECTOR_FOOTER).css('position') === 'fixed') {
          sidebarHeight = heights.window - heights.header - heights.footer;
        }

        $controlSidebar.css('height', sidebarHeight);

        if (jQuery.fn.overlayScrollbars !== undefined) {
          $controlSidebar.overlayScrollbars({
            className: this._config.scrollbarTheme,
            sizeAutoCapable: true,
            scrollbars: {
              autoHide: this._config.scrollbarAutoHide,
              clickScrolling: true
            }
          });
        }
      }

      // Static

      static _jQueryInterface(operation) {
        return this.each(function () {
          let data = jQuery(this).data(DATA_KEY);
          const _options = jQuery.extend({}, Default, jQuery(this).data());

          if (!data) {
            data = new ControlSidebar(this, _options);
            jQuery(this).data(DATA_KEY, data);
          }

          if (data[operation] === 'undefined') {
            throw new Error(`${operation} is not a function`)
          }

          data[operation]();
        })
      }
    }

    /**
     *
     * Data Api implementation
     * ====================================================
     */
    jQuery(document).on('click', SELECTOR_DATA_TOGGLE, function (event) {
      event.preventDefault();
      ControlSidebar._jQueryInterface.call(jQuery(this), 'toggle');
    });

    jQuery(document).ready(() => {
      ControlSidebar._jQueryInterface.call(jQuery(SELECTOR_DATA_TOGGLE), '_init');
    });

    /**
     * jQuery API
     * ====================================================
     */

    jQuery.fn[NAME] = ControlSidebar._jQueryInterface;
    jQuery.fn[NAME].Constructor = ControlSidebar;
    jQuery.fn[NAME].noConflict = function () {
      jQuery.fn[NAME] = JQUERY_NO_CONFLICT;
      return ControlSidebar._jQueryInterface
    };

    exports.CardWidget = CardWidget;
    exports.ControlSidebar = ControlSidebar;
    exports.DirectChat = DirectChat;
    exports.FullScreen = FullScreen;
    exports.Layout = Layout;
    exports.PushMenu = PushMenu;
    exports.SidebarSearch = SidebarSearch;
    exports.Treeview = Treeview;
    exports.onDOMContentLoaded = onDOMContentLoaded;
    exports.slideDown = slideDown;
    exports.slideToggle = slideToggle;
    exports.slideUp = slideUp;

}));

