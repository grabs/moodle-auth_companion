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

use auth_companion\globals as gl;

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
    protected $mainuser;
    /** @var \stdClass */
    protected $companion;

    /**
     * Constructor.
     *
     * @param \stdClass|null $user
     */
    public function __construct($user = null) {
        global $USER;

        if (empty($user)) {
            $user = $USER;
        }
        $this->mainuser  = $user;
        $this->companion = $this->get_companion_record();
        $this->set_companion_attributes();
    }

    /**
     * Login the companion account.
     *
     * @throws \moodle_exception
     * @return \stdClass|bool    the user record or false
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
     * Login back with the main account.
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

        $user = $DB->get_record('user', ['id' => $this->get_mainuser_id()]);
        $user = get_complete_user_data('id', $user->id, $CFG->mnet_localhost_id);
        $user = complete_user_login($user);

        return $user;
    }

    /**
     * Enrol the companion account into the given course.
     *
     * @param  \stdClass         $course The course the account is enrolled to
     * @param  int               $roleid
     * @throws \moodle_exception
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
        $unassignparams = [
            'userid'    => $this->companion->id,
            'contextid' => $coursecontext->id,
        ];
        role_unassign_all($unassignparams, true);
        role_assign($roleid, $this->companion->id, $coursecontext->id);
    }

    /**
     * Get the id of the companion account. This in fact is a userid of this account.
     *
     * @return int
     */
    public function get_companion_id() {
        global $DB;

        if (!empty($this->companion->id)) {
            return $this->companion->id;
        }

        if ($companionlink = $DB->get_record('auth_companion_accounts', ['mainuserid' => $this->get_mainuser_id()])) {
            return (int) $companionlink->companionid;
        }

        return 0;
    }

    /**
     * Get the userid of the mainuser account.
     *
     * @return int
     */
    public function get_mainuser_id() {
        return $this->mainuser->id;
    }

    /**
     * Get the record of the companion account.
     *
     * @return \stdClass The companion account record
     */
    protected function get_companion_record() {
        global $DB;

        // Does a companion user exist?
        if ($companionid = $this->get_companion_id()) {
            $params = [
                'id'      => $companionid,
                'auth'    => gl::AUTH,
                'deleted' => 0,
            ];
            if ($companion = $DB->get_record('user', $params)) {
                return $companion;
            }
        }

        // The companion user does not exist yet.
        $companion           = new \stdClass();
        $companion->username = \core\uuid::generate(); // Get a unique username.
        $companion->password = generate_password();

        $newrecord = create_user_record($companion->username, $companion->password, gl::AUTH);
        $this->set_companion_id($newrecord->id);
        foreach ($newrecord as $key => $val) {
            $companion->{$key} = $val;
        }

        return $companion;
    }

    /**
     * Set the mandatory attributes like first- and lastname to the companion account.
     *
     * @return boot True on success
     */
    public function set_companion_attributes() {
        global $DB;
        $mycfg = gl::mycfg();

        $this->companion->firstname = $this->mainuser->firstname;
        $this->companion->lastname  = $this->mainuser->lastname . ' ' . $mycfg->namesuffix;
        $this->companion->email     = $this->companion->username . '@companion.invalid';

        return $DB->update_record('user', $this->companion);
    }

    /**
     * Override the email address of the companion user with the address of the mainuser.
     *
     * @return bool
     */
    public function override_email() {
        global $DB;

        $this->companion->email = $this->mainuser->email;

        return $DB->update_record('user', $this->companion);
    }

    /**
     * Set the companion id for a user.
     *
     * @param  int  $companionid
     * @return bool
     */
    protected function set_companion_id($companionid) {
        global $DB;

        $mainuserid = $this->get_mainuser_id();
        if ($companionlink = $DB->get_record('auth_companion_accounts', ['mainuserid' => $mainuserid])) {
            return $DB->set_field('auth_companion_accounts', 'companionid', $companionid, ['mainuserid' => $mainuserid]);
        }
        $companionlink              = new \stdClass();
        $companionlink->mainuserid  = $mainuserid;
        $companionlink->companionid = $companionid;
        $companionlink->timecreated = time();

        return (bool) $DB->insert_record('auth_companion_accounts', $companionlink);
    }

    /**
     * Get an instance from the current companion account.
     * The main account has an companionid stored in table auth_companion_accounts.
     *
     * @param  int    $companionuserid
     * @return static
     */
    public static function get_instance_by_companion($companionuserid) {
        global $DB;

        // First we get the id of the related main user.
        $mainuserid = self::get_mainuser_id_from_companion($companionuserid);

        if (empty($mainuser = $DB->get_record('user', ['id' => $mainuserid], '*', MUST_EXIST))) {
            throw new \moodle_exception('wrong userid');
        }

        return new static($mainuser);
    }

    /**
     * Get the user id of a companion account.
     *
     * @param  int $companionid
     * @return int
     */
    protected static function get_mainuser_id_from_companion($companionid) {
        global $DB;

        if ($companionlink = $DB->get_record('auth_companion_accounts', ['companionid' => $companionid])) {
            return (int) $companionlink->mainuserid;
        }

        return 0;
    }
}
