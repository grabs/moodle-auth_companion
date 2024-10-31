<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Collection of functions.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_companion\globals as gl;

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function auth_companion_render_navbar_output() {
    if (\auth_companion\util::is_enabled()) {
        return \auth_companion\util::create_nav_action();
    }
}

/**
 * Modify the user navigation.
 *
 * @param navigation_node $navigation
 */
function auth_companion_extend_navigation_user_settings(navigation_node $navigation) {
    if (\auth_companion\util::is_enabled()) {
        \auth_companion\util::set_user_menu();
    }
}

/**
 * Get icon mapping for FontAwesome.
 *
 * @return array
 */
function auth_companion_get_fontawesome_icon_map() {
    // We build a map of some icons we use in the top usermenu.
    $iconmap = [
        'auth_companion:companionon'  => 'fa-user-secret text-success',
        'auth_companion:companionoff' => 'fa-user-secret',
    ];

    return $iconmap;
}
