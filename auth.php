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
 * @package   auth_companion
 * @copyright 2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author    Andreas Grabs <moodle@grabs-edv.de>
 * @license   http:   //www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
use \auth_companion\globals as gl;

/**
 * Plugin for no authentication - disabled user.
 */
class auth_plugin_companion extends auth_plugin_base {

    public const COMPONENT = 'auth_'.gl::AUTH;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'companion';
        $this->config = get_config(self::COMPONENT);

        // Force some profile fields to locked.
        $this->config->field_lock_firstname = 'locked';
        $this->config->field_lock_lastname  = 'locked';
        $this->config->field_lock_email     = 'locked';

        // Remove the forced locked fields from internal userfields list.
        $userfields = $this->userfields;
        $this->userfields = array();
        $fieldstoremove = array('firstname', 'lastname', 'email');
        foreach ($userfields as $field) {
            if (in_array($field, $fieldstoremove)) {
                continue;
            }
            $this->userfields[] = $field;
        }
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_companion() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Do not allow any login.
     *
     */
    function user_login($username, $password) {
        // global $CFG, $DB, $SESSION;
        global $CFG, $DB;
        // if (empty($SESSION->auth_companion_login)) {
        //     return false;
        // }
        if (!$user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return false;
        }
        if (!validate_internal_user_password($user, $password)) {
            return false;
        }
        return true;
    }

    /**
     * No password updates.
     */
    function user_update_password($user, $newpassword) {
        return false;
    }

    function prevent_local_passwords() {
        // just in case, we do not want to loose the passwords
        return false;
    }

    /**
     * No external data sync.
     *
     * @return bool
     */
    function is_internal() {
        //we do not know if it was internal or external originally
        return true;
    }

    /**
     * No changing of password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * No password resetting.
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return true;
    }

    /**
     * Returns information on how the specified user can change their password.
     * User accounts with authentication type set to companion are disabled accounts.
     * They cannot change their password.
     *
     * @param stdClass $user A user object
     * @return string[] An array of strings with keys subject and message
     */
    public function get_password_change_info(stdClass $user) : array {
        $site = get_site();

        $data = new stdClass();
        $data->firstname = $user->firstname;
        $data->lastname  = $user->lastname;
        $data->username  = $user->username;
        $data->sitename  = format_string($site->fullname);
        $data->admin     = generate_email_signoff();

        $message = get_string('emailpasswordchangeinfodisabled', '', $data);
        $subject = get_string('emailpasswordchangeinfosubject', '', format_string($site->fullname));

        return [
            'subject' => $subject,
            'message' => $message
        ];
    }
}


