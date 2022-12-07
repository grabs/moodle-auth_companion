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
 * Observer for events.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Delete linked companion accounts for the deleted user.
     *
     * @param \core\event\user_deleted $event
     * @return boolean
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        $DB = gl::db();

        if (!is_enabled_auth(gl::AUTH)) {
            return true;
        }
        $userid = $event->objectid;
        $deleteduser = $event->get_record_snapshot('user', $userid);
        if ($deleteduser->auth == gl::AUTH) {
            return; // Companion accounts do not have a companion too.
        }

        // Is there a related companion account?
        if ($companionrecord = $DB->get_record('auth_companion_accounts', array('mainuserid' => $userid))) {
            \auth_companion\util::delete_companionuser($companionrecord->companionid);
        }
    }
}
