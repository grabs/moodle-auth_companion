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
 * Provide a set of static methods.
 *
 * @copyright  2022 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {
    public static function set_user_menu() {
        $CFG = gl::cfg();
        $PAGE = gl::page();

        // Do not manipulate the custum user menu on an admin page.
        if (preg_match('#^admin.*#', $PAGE->pagetype)) {
            return;
        }

        // There are two situations we can run in.
        // 1) The user is a companion account.
        // 2) The user is not a companion account.
        // In case 1) the menu item to leave the account can be show everywhere.
        // In case 2) the menu item should only be shown inside a course page.

        // The user is a companion.
        if (static::is_companion()) {
            $FULLME = gl::fullme();
            $backurl = new \moodle_url($FULLME);
            $leaveurl = new \moodle_url('/auth/companion/leave.php', array('backurl' => $backurl->out()));
            $leavename = s(get_string('switch_back', 'auth_companion'));
            $CFG->customusermenuitems .= "\n###\n$leavename|" . $leaveurl->out();
            return;
        }

        // The user is not a companion.
        if (!has_capability('auth/companion:allowcompanion', $PAGE->context)) {
            return;
        }
        // We have to check whether or not the current page is a course page.
        if (!static::page_is_course()) {
            return;
        }

        $enterurl = new \moodle_url('/auth/companion/enter.php', array('courseid' => $PAGE->course->id));
        $entername = s(get_string('switch_to_companion', 'auth_companion'));
        $CFG->customusermenuitems .= "\n###\n$entername|" . $enterurl->out();
        return;
    }

    public static function is_companion($user = null) {
        $USER = gl::user();

        if (empty($user)) {
            $user = $USER;
        }
        return $USER->auth == 'companion';
    }

    public static function page_is_course() {
        $PAGE = gl::page();
        if ($PAGE->context->contextlevel != CONTEXT_COURSE) {
            return false;
        }
        if ($PAGE->course->id == SITEID) {
            return false;
        }
        return true;
    }

    public static function delete_user($userid) {
        $DB = gl::db();
        $CFG = gl::cfg();
        $mycfg = gl::mycfg();

        if (is_siteadmin($userid)) {
            throw new \moodle_exception('useradminodelete', 'error');
        }
        $user = $DB->get_record('user', array('id'=>$userid, 'mnethostid'=>$CFG->mnet_localhost_id), '*', MUST_EXIST);

        // First we anonymize the username and email.
        $anonymousname = empty($mycfg->anonymousname) ? 'anonymous' : $mycfg->anonymousname;
        $user->firstname = $anonymousname;
        $user->lastname = $anonymousname;
        $user->email = $anonymousname . '.' . $anonymousname . '@auth-companion.invalid';
        $DB->update_record('user', $user);

        if (!delete_user($user)) {
            throw new \moodle_exception('could not delete user');
        }
        \core\session\manager::gc(); // Remove stale sessions.
    }
}
