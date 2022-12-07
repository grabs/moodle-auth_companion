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

namespace auth_companion;
use \auth_companion\globals as gl;

/**
 * Represents a companion user.
 *
 * @copyright  2022 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class companion {
    private $companion;
    private $mainuserid;

    public function __construct($user = null, $forcecreate = false) {
        $USER = gl::user();

        if (empty($user)) {
            $user = $USER;
        }
        $this->mainuserid = $user->id;
        $this->companion = self::get_companion_record($user, $forcecreate);
        if ($forcecreate) {
            set_user_preference('auth_companion_user', $this->get_id(), $user);
        }
    }

    public function login() {
        $DB = gl::db();
        // $SESSION = gl::session();

        if (!is_enabled_auth(gl::AUTH)) {
            return;
        }

        // $SESSION->auth_companion_login = true;
        $password = generate_password();
        $this->companion->password = hash_internal_user_password($password);
        $DB->update_record('user', $this->companion);
        $user = authenticate_user_login($this->companion->username, $password);
        if (!$user = complete_user_login($user)) {
            require_logout();
            throw new \moodle_exception('could not login companion user');
        }

        return $user;
    }

    public function relogin_main() {
        $DB = gl::db();
        $CFG = gl::cfg();
        // $SESSION = gl::session();

        if (!is_enabled_auth(gl::AUTH)) {
            return;
        }

        $user = $DB->get_record('user', array('id' => $this->mainuserid));
        $user = get_complete_user_data('id', $user->id, $CFG->mnet_localhost_id);
        $user = complete_user_login($user);
        return $user;
    }

    public function enrol($course) {
        $manual = enrol_get_plugin('manual');

        $coursecontext = \context_course::instance($course->id);
        $instancefound = false;
        if ($instances = enrol_get_instances($course->id, false)) {
            foreach ($instances as $instance) {
                if ($instance->enrol === 'manual') {
                    $instancefound = true;
                    break;
                }
            }
        }
        if (!$instancefound) {
            throw new \moodle_exception('No manual instance found!');
        }
        $manual->enrol_user($instance, $this->companion->id, null, 0, 0);

    }

    public function get_id() {
        return $this->companion->id;
    }

    protected static function get_companion_record($user, $forcecreate = false) {
        $DB = gl::db();
        $mycfg = gl::mycfg();

        // Does a companion user exist?
        $companionusername = $user->id . gl::USERNAME_SUFFIX;
        if ($companion = $DB->get_record('user', array('username' => $companionusername, 'auth' => 'companion'))) {
            return $companion;
        }

        $companion = new \stdClass();
        $companion->username = $companionusername;
        $companion->password = generate_password();
        if ($forcecreate) {
            $newrecord = create_user_record($companion->username, $companion->password, 'companion');
            foreach ($newrecord as $key => $val) {
                $companion->{$key} = $val;
            }

        }
        $companion->firstname = $user->firstname;
        $companion->lastname = $user->lastname . ' ' . $mycfg->namesuffix;
        $companion->email = $companion->username . '@companion.invalid';
        return $companion;
    }

    /**
     * Get an instance from the current companion account.
     * The main account has a user preference "auth_companion_user" which is the id of the companion user.
     * The current logged in companion user has the user preference "auth_companion_mainuser".
     * The current companion can only login back to the real user if the stored preferences are right.
     *
     * @param \stdClass $companion
     * @return static
     */
    public static function get_instance_by_companion($companionuserid) {
        $DB = gl::db();

        // First we get the id of the related main user.
        $mainuserid = get_user_preferences('auth_companion_mainuser', null, $companionuserid);

        // Check whether or not the current companion is related to the main user.
        // This is done by checking the user preference "auth_companion_user" of the main user.
        // This id must be the id of the current companion user.
        $companionid = get_user_preferences('auth_companion_user', null, $mainuserid);
        if ($companionid != $companionuserid) {
            throw new \moodle_exception('wrong userid');
        }
        $mainuser = $DB->get_record('user', array('id' => $mainuserid), '*', MUST_EXIST);
        return new static($mainuser);
    }
}
