#!/usr/bin/env python3
"""Build bootstrap-icons-subset.css for cassiopeia_admin_theme."""

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "libraries/bootstrap-icons/font/bootstrap-icons.min.css"
OUT = ROOT / "themes/cassiopeia_admin_theme/css/bootstrap-icons-subset.css"

ICONS = [
    "list", "search", "arrows-fullscreen", "fullscreen-exit", "grid-fill",
    "chevron-right", "chevron-down", "chevron-left", "grid", "speedometer",
    "speedometer2", "gear", "gear-fill", "house", "house-door", "pencil",
    "pencil-square", "trash", "trash-fill", "plus", "plus-lg", "plus-circle",
    "dash-lg", "dash", "x-lg", "x", "x-circle", "box-arrow-right", "folder",
    "people", "person", "bell", "envelope", "file-earmark", "collection",
    "layers", "table", "graph-up", "database", "shield", "lock", "unlock",
    "eye", "bookmark", "star", "circle", "check", "arrow-repeat", "box",
    "diagram-3", "menu-button", "layout-sidebar", "display", "cpu", "cloud",
    "calendar", "cart", "tag", "link", "globe", "info-circle", "question-circle",
    "exclamation-triangle", "sliders", "wrench", "three-dots", "list-ul",
    "grid-3x3-gap", "box-seam", "archive", "inbox", "send", "download", "upload",
    "filter", "sort-down", "sort-up", "arrow-left", "arrow-right", "caret-down",
    "caret-right", "pencil-fill", "house-fill",
]

HEADER = """/*!
 * Bootstrap Icons subset for Cassiopeia admin theme.
 * Subset of rules from bootstrap-icons v1.11.3; font files remain in libraries/bootstrap-icons.
 */
@font-face{font-display:block;font-family:bootstrap-icons;src:url(/libraries/bootstrap-icons/font/fonts/bootstrap-icons.woff2?dd67030699838ea613ee6dbda90effa6) format("woff2"),url(/libraries/bootstrap-icons/font/fonts/bootstrap-icons.woff?dd67030699838ea613ee6dbda90effa6) format("woff")}
.bi::before,[class*=" bi-"]::before,[class^=bi-]::before{display:inline-block;font-family:bootstrap-icons!important;font-style:normal;font-weight:400!important;font-variant:normal;text-transform:none;line-height:1;vertical-align:-.125em;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
"""

def main() -> None:
    css = SRC.read_text(encoding="utf-8")
    rules: list[str] = []
    missing: list[str] = []
    for name in ICONS:
        match = re.search(
            r"\.bi-" + re.escape(name) + r"::before\{content:\"([^\"]+)\"\}",
            css,
        )
        if match:
            rules.append(f'.bi-{name}::before{{content:"{match.group(1)}"}}')
        else:
            missing.append(name)

    OUT.write_text(HEADER + "".join(rules) + "\n", encoding="utf-8")
    print(f"Wrote {OUT} ({len(rules)} rules, {len(missing)} missing)")
    if missing:
        print("Missing:", ", ".join(missing[:20]))


if __name__ == "__main__":
    main()
