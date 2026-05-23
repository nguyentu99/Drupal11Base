<?php

/**
 * @file
 * Hooks provided by the Cassiopeia Admin module.
 */

/**
 * Alter administrator sidebar menu blocks before rendering.
 *
 * @param array $blocks
 *   List of block arrays with keys: label, icon_class, open, links.
 */
function hook_cassiopeia_admin_menu_blocks_alter(array &$blocks): void {
}

/**
 * Alter administrator sidebar menu blocks before rendering (legacy).
 *
 * @deprecated in cassiopeia_admin:8.x-1.x
 *   Use hook_cassiopeia_admin_menu_blocks_alter() instead. Both hooks are invoked.
 *
 * @param array $blocks
 *   Same structure as hook_cassiopeia_admin_menu_blocks_alter().
 */
function hook_cassiopeia_admin_menu_alter(array &$blocks): void {
}
