const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
const SELECTOR_CONTROL_SIDEBAR_WRAPPER = ".control-sidebar-content";
const Default = {
  scrollbarTheme: "os-theme-light",
  scrollbarAutoHide: "leave",
  scrollbarClickScroll: true,
};

document.addEventListener("DOMContentLoaded", function () {
  const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
  const sidebarWControlrapper = document.querySelector(SELECTOR_CONTROL_SIDEBAR_WRAPPER);
  if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined") {
    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
      scrollbars: {
        theme: Default.scrollbarTheme,
        autoHide: Default.scrollbarAutoHide,
        clickScroll: Default.scrollbarClickScroll,
      },
    });
  }
  if (sidebarWControlrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined") {
    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWControlrapper, {
      scrollbars: {
        theme: Default.scrollbarTheme,
        autoHide: Default.scrollbarAutoHide,
        clickScroll: Default.scrollbarClickScroll,
      },
    });
  }
});
