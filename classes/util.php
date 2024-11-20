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
 * Provide a set of static methods.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {
    /** Default notsendigemaildomain */
    const DEFAULT_EMAILDOMAIN = 'companion.invalid';

    /**
     * Modify the user menu by adding the "switch to" or "switch back" buttons.
     *
     * @return void
     */
    public static function set_user_menu() {
        global $CFG, $PAGE, $FULLME;

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
            $backurl   = new \moodle_url($FULLME);
            $leaveurl  = new \moodle_url('/auth/companion/leave.php', ['backurl' => $backurl->out()]);
            $leavename = s(get_string('switch_back', 'auth_companion'));
            $CFG->customusermenuitems .= "\n###\n$leavename|" . $leaveurl->out();

            return;
        }

        // The user is not a companion.
        // So we check the other way, whether the user can become a companion.

        // We have to check whether or not the current page is in a course page.
        if (!static::page_is_in_course()) {
            return;
        }
        // Do we have the right capability?
        if (!has_capability('auth/companion:allowcompanion', $PAGE->context)) {
            return;
        }

        $enterurl  = new \moodle_url('/auth/companion/enter.php', ['courseid' => $PAGE->course->id]);
        $entername = s(get_string('switch_to_companion', 'auth_companion'));
        $CFG->customusermenuitems .= "\n###\n$entername|" . $enterurl->out();
    }

    /**
     * Create a rendered action element for user navigation (Top navigation left from user avatar).
     *
     * @return string The html
     */
    public static function create_nav_action() {
        global $OUTPUT, $PAGE, $FULLME;

        if (!static::page_is_in_course()) {
            return '';
        }

        // The user is a companion.
        if (static::is_companion()) {
            $backurl = new \moodle_url($FULLME);
            $url     = new \moodle_url('/auth/companion/leave.php', ['backurl' => $backurl->out()]);
            $text    = get_string('switch_back', 'auth_companion');
            $pixicon = 'companionon';
        } else {
            if (!has_capability('auth/companion:allowcompanion', $PAGE->context)) {
                return '';
            }

            $url     = new \moodle_url('/auth/companion/enter.php', ['courseid' => $PAGE->course->id]);
            $text    = get_string('switch_to_companion', 'auth_companion');
            $pixicon = 'companionoff';
        }

        $icon = new \pix_icon($pixicon, $text, 'auth_companion');

        $content       = new \stdClass();
        $content->text = $text;
        $content->url  = $url;
        $content->icon = $OUTPUT->render($icon);

        return $OUTPUT->render_from_template('auth_companion/navbar_action', $content);
    }

    /**
     * Checks whether or not the given user is a companion account.
     *
     * @param  \stdClass|null $user
     * @return bool
     */
    public static function is_companion($user = null) {
        global $USER;

        if (empty($user)) {
            $user = $USER;
        }

        // In some circumstances, the user object may not have an auth property or is completely empty.
        if (empty($user->auth)) {
            return false;
        }

        return $user->auth == gl::AUTH;
    }

    /**
     * Checks whether or not the current page is a course page.
     *
     * @return bool
     */
    public static function page_is_in_course() {
        global $COURSE;

        // This is a page on which the course is not set and maybe wrong coded.
        if (empty($COURSE->id)) {
            return false;
        }
        // This is the frontpage or any other system page.
        if ($COURSE->id == SITEID) {
            return false;
        }
        return true;
    }

    /**
     * Delete the companion account.
     *
     * @param  int               $userid
     * @param  bool              $iscompanionid if true the id means the companion account otherwise it means the main userid
     * @throws \moodle_exception
     * @return void
     */
    public static function delete_companionuser(int $userid, bool $iscompanionid = true) {
        global $DB, $CFG;
        $mycfg = gl::mycfg();

        if (is_siteadmin($userid)) {
            throw new \moodle_exception('useradminodelete', 'error');
        }
        if (empty($iscompanionid)) {
            if (!$companionid = $DB->get_field('auth_companion_accounts', 'companionid', ['mainuserid' => $userid])) {
                return;
            }
        } else {
            $companionid = $userid;
        }

        $params = [
            'id'         => $companionid,
            'auth'       => gl::AUTH,
            'deleted'    => 0,
            'mnethostid' => $CFG->mnet_localhost_id,
        ];
        if ($user = $DB->get_record('user', $params, '*', IGNORE_MISSING)) {
            $emaildomain = empty($mycfg->emaildomain) ? static::DEFAULT_EMAILDOMAIN : $mycfg->emaildomain;

            // First we anonymize the username and email.
            $anonymousname   = $mycfg->anonymousname ?? 'anonymous';
            $user->firstname = $anonymousname;
            $user->lastname  = $anonymousname;
            $user->email     = $anonymousname . '.' . $anonymousname . '@auth-' . $emaildomain;
            $DB->update_record('user', $user);

            if (!delete_user($user)) {
                throw new \moodle_exception('could not delete user');
            }
            $DB->set_field('user', 'auth', 'nologin', ['id' => $companionid]);
        }

        $DB->delete_records('auth_companion_accounts', ['companionid' => $companionid]);
        \core\session\manager::gc(); // Remove stale sessions.
    }

    /**
     * Delete all unrelated companion accounts.
     *
     * @return void
     */
    public static function clean_old_accounts() {
        global $DB;

        $sql = 'SELECT c.*, u.id AS relateduserid
                FROM {auth_companion_accounts} c
                LEFT JOIN {user} u ON c.mainuserid = u.id AND u.deleted = 0
                WHERE u.id IS NULL
        ';

        $recordset = $DB->get_recordset_sql($sql, null);
        foreach ($recordset as $record) {
            // There is no real user related to this record.
            try {
                mtrace('... Remove orphaned companion user (' . $record->companionid . ')');
                static::delete_companionuser($record->companionid);
            } catch (\moodle_exception $e) {
                $DB->delete_records('auth_companion_accounts', ['companionid' => $record->companionid]);
            }
        }
        $recordset->close();
    }

    /**
     * Get role options for usage in a select form element.
     *
     * @param  string   $capability
     * @param  ?\context $context
     * @return array    the roles array with localized names
     */
    public static function get_roles_options(string $capability, ?\context $context = null) {
        $return = [];

        if (!$roles = get_roles_with_capability($capability, CAP_ALLOW, $context)) {
            return $return;
        }
        foreach ($roles as $role) {
            $return[$role->id] = $role->shortname;
        }

        $roles = role_fix_names($return, $context);
        $roles = ['' => get_string('choose')] + $roles;
        return $roles;
    }

    /**
     * Get the role id of a given shortname.
     *
     * @param string $shortname
     * @return int|bool The role id or false if $shortname does not exist.
     */
    public static function get_roleid_from_name(string $shortname) {
        global $DB;

        if ($roleid = $DB->get_field('role', 'id', ['shortname' => $shortname])) {
            return $roleid;
        }
        return false;
    }

    /**
     * Checks if the given email domain is valid for the auth_companion plugin.
     *
     * @param string $domain The email domain to validate.
     * @return bool True if the email domain is valid, false otherwise.
     */
    public static function validate_emaildomain($domain): bool {
        $dummyaddress = 'dummy@' . $domain;
        return validate_email($dummyaddress);
    }

    /**
     * Checks if the auth_companion plugin is enabled.
     *
     * @return bool True if the auth_companion plugin is enabled, false otherwise.
     */
    public static function is_enabled(): bool {
        $config = get_config('auth_companion');
        if (!is_enabled_auth(gl::AUTH)) {
            return false;
        }
        if (!static::validate_emaildomain($config->emaildomain)) {
            return false;
        }
        return true;
    }

    /**
     * Require that the auth_companion plugin is enabled.
     *
     * This method throws a moodle_exception if the auth_companion plugin is not enabled.
     */
    public static function require_enabled() {
        if (static::is_enabled()) {
            return;
        }
        throw new \moodle_exception('auth_companion_disabled');
    }
}
