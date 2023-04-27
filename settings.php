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
 * Plugin settings page
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
use \auth_companion\globals as gl;

if ($ADMIN->fulltree) {

    $settings->add(
        new admin_setting_heading(
            'auth_companion/hdr1',
            get_string('general'),
            new lang_string('auth_companiondescription', 'auth_companion')
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'auth_companion/namesuffix',
            get_string('setting_namesuffix', 'auth_companion'),
            get_string('setting_namesuffix_help', 'auth_companion'),
            '(companion)'
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'auth_companion/forcelogin',
            get_string('setting_forcelogin', 'auth_companion'),
            get_string('setting_forcelogin_help', 'auth_companion'),
            true
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'auth_companion/forcedeletedata',
            get_string('setting_forcedeletedata', 'auth_companion'),
            get_string('setting_forcedeletedata_help', 'auth_companion'),
            true
        )
    );

    // Prevent email overriding if $CFG->authloginviaemail is active.
    if (!empty($CFG->authloginviaemail)) {
        $options = array(
            gl::EMAILNOOVERRIDE => get_string('setting_email_option_no_override', 'auth_companion'),
        );
    } else {
        $options = array(
            gl::EMAILNOOVERRIDE       => get_string('setting_email_option_no_override', 'auth_companion'),
            gl::EMAILFORCEOVERRIDE    => get_string('setting_email_option_force_override', 'auth_companion'),
            gl::EMAILOPTIONALOVERRIDE => get_string('setting_email_option_optional', 'auth_companion'),
        );
    }
    $settings->add(
        new admin_setting_configselect(
            'auth_companion/emailoverride',
            get_string('setting_email_options', 'auth_companion'),
            get_string('setting_email_option_help', 'auth_companion'),
            gl::EMAILNOOVERRIDE,
            $options
        )
    );

    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin(gl::AUTH);

    display_auth_lock_options($settings, $authplugin->authtype,
        $authplugin->userfields, get_string('auth_fieldlocks_help', 'auth'), false, false);

}
