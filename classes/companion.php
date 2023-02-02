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

namespace auth_companion;
use \auth_companion\globals as gl;

/**
 * Represents a companion user.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class companion {
    /** @var \stdClass */
    protected $companion;
    /** @var int */
    protected $mainuserid;

    /**
     * Constructor
     *
     * @param \stdClass|null $user
     * @param bool $forcecreate
     */
    public function __construct($user = null, $forcecreate = false) {
        global $USER;

        if (empty($user)) {
            $user = $USER;
        }
        $this->mainuserid = $user->id;
        $this->companion = self::get_companion_record($user, $forcecreate);
    }

    /**
     * Login the companion account
     *
     * @throws \moodle_exception
     * @return \stdClass|bool The user record or false.
     */
    public function login() {

        if (!is_enabled_auth(gl::AUTH)) {
            return false;
        }

        // Recreate a new empty session.
        \core\session\manager::init_empty_session(true);

        if (!$user = complete_user_login($this->companion)) {
            require_logout();
            throw new \moodle_exception('could not login companion user');
        }

        return $user;
    }

    /**
     * Login back with the main account
     *
     * @return \stdClass|bool The main account record or false
     */
    public function relogin_main() {
        global $DB, $CFG;

        if (!is_enabled_auth(gl::AUTH)) {
            return false;
        }

        // Recreate a new empty session.
        \core\session\manager::init_empty_session(true);

        $user = $DB->get_record('user', array('id' => $this->mainuserid));
        $user = get_complete_user_data('id', $user->id, $CFG->mnet_localhost_id);
        $user = complete_user_login($user);
        return $user;
    }

    /**
     * Enrol the companion account into the given course
     *
     * @throws \moodle_exception
     * @param \stdClass $course The course the account is enrolled to
     * @param int $roleid
     * @return void
     */
    public function enrol($course, $roleid) {
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
        $unassignparams = array(
            'userid' => $this->companion->id,
            'contextid' => $coursecontext->id,
        );
        role_unassign_all($unassignparams, true);
        role_assign($roleid, $this->companion->id, $coursecontext->id);
    }

    /**
     * Get the id of the companion account. This in fact is a userid of this account.
     *
     * @return int
     */
    public function get_id() {
        return $this->companion->id;
    }

    /**
     * Get the record of the companion account
     *
     * @param \stdClass $user The record of the user the companion is related to.
     * @param boolean $forcecreate
     * @return \stdClass The companion account record
     */
    protected static function get_companion_record($user, $forcecreate = false) {
        global $DB;
        $mycfg = gl::mycfg();

        // Does a companion user exist?
        $companionid = static::get_companion_id_from_user($user);
        $params = array(
            'id'      => $companionid,
            'auth'    => gl::AUTH,
            'deleted' => 0,
        );
        if ($companion = $DB->get_record('user', $params)) {
            return $companion;
        }

        // The companion user does not exist yet.
        $companion = new \stdClass();
        $companion->username = \core\uuid::generate(); // Get a unique username.
        $companion->password = generate_password();
        if ($forcecreate) {
            $newrecord = create_user_record($companion->username, $companion->password, gl::AUTH);
            self::set_companion_id_for_user($user, $newrecord->id);
            foreach ($newrecord as $key => $val) {
                $companion->{$key} = $val;
            }

        }
        $companion->firstname = $user->firstname;
        $companion->lastname = $user->lastname . ' ' . $mycfg->namesuffix;
        $companion->email = $companion->username . '@companion.invalid';
        $DB->update_record('user', $companion);
        return $companion;
    }

    /**
     * Get a companion id from a user
     *
     * @param \stdClass $user
     * @return int
     */
    protected static function get_companion_id_from_user($user) {
        global $DB;

        if ($companionlink = $DB->get_record('auth_companion_accounts', array('mainuserid' => $user->id))) {
            return (int) $companionlink->companionid;
        }
        return 0;
    }

    /**
     * Get the user id of a companion account
     *
     * @param int $companionid
     * @return int
     */
    protected static function get_user_id_from_companion($companionid) {
        global $DB;

        if ($companionlink = $DB->get_record('auth_companion_accounts', array('companionid' => $companionid))) {
            return (int) $companionlink->mainuserid;
        }
        return 0;
    }

    /**
     * Set the companion id for a user
     *
     * @param \stdClass $user
     * @param int $companionid
     * @return bool
     */
    protected static function set_companion_id_for_user($user, $companionid) {
        global $DB;

        if ($companionlink = $DB->get_record('auth_companion_accounts', array('mainuserid' => $user->id))) {
            return $DB->set_field('auth_companion_accounts', 'companionid', $companionid, array('mainuserid' => $user->id));
        }
        $companionlink = new \stdClass();
        $companionlink->mainuserid = $user->id;
        $companionlink->companionid = $companionid;
        $companionlink->timecreated = time();
        return (bool) $DB->insert_record('auth_companion_accounts', $companionlink);
    }

    /**
     * Get an instance from the current companion account.
     * The main account has an companionid stored in table auth_companion_accounts.
     *
     * @param int $companionuserid
     * @return static
     */
    public static function get_instance_by_companion($companionuserid) {
        global $DB;

        // First we get the id of the related main user.
        $mainuserid = self::get_user_id_from_companion($companionuserid);

        if (empty($mainuser = $DB->get_record('user', array('id' => $mainuserid), '*', MUST_EXIST))) {
            throw new \moodle_exception('wrong userid');
        }
        return new static($mainuser);
    }
}
