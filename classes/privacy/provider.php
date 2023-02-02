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

namespace auth_companion\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem for auth_companion.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Get all contexts contain user information for the given user.
     *
     * @param int $userid the id of the user.
     * @return contextlist the list of contexts containing user information.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {auth_companion_accounts} ca
                  JOIN {context} ctx ON ctx.instanceid = ca.mainuserid AND ctx.contextlevel = :contextlevel
                 WHERE ca.mainuserid = :userid";
        $params = ['userid' => $userid, 'contextlevel' => CONTEXT_USER];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the user in the identified contexts.
     *
     * @param approved_contextlist $contextlist the list of approved contexts for the user.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        $linkedlogin = $DB->get_record(
            'auth_companion_accounts',
            array('mainuserid' => $user->id),
            'companionid, timecreated'
        );

        if (!$companionrecord = $DB->get_record('user', array('id' => $linkedlogin->companionid))) {
            return;
        }
        $data = (object)[
            'companionid' => $linkedlogin->companionid,
            'timecreated' => transform::datetime($linkedlogin->timecreated),
            'companionfirstname' => $companionrecord->firstname,
            'companionlastname' => $companionrecord->lastname,
        ];
        writer::with_context(\context_user::instance($user->id))->export_data([
            get_string('privacy:metadata:auth_companion', 'auth_companion'), $linkedlogin->companionid
        ], $data);

    }

    /**
     * Delete all user data for this context.
     *
     * @param  \context $context The context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        static::delete_user_data($context->instanceid);
    }

    /**
     * Delete user data in the list of given contexts.
     *
     * @param approved_contextlist $contextlist the list of contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER) {
                continue;
            }
            if ($context->instanceid == $userid) {
                static::delete_user_data($context->instanceid);
            }
        }
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $sql = "SELECT mainuserid
                  FROM {auth_companion_accounts}
                 WHERE mainuserid = ?";
        $params = [$context->instanceid];
        $userlist->add_from_sql('mainuserid', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            static::delete_user_data($context->instanceid);
        }
    }

    /**
     * Description of the metadata stored for users in auth_companion.
     *
     * @param collection $collection a collection to add to.
     * @return collection the collection, with relevant metadata descriptions for auth_companion added.
     */
    public static function get_metadata(collection $collection): collection {
        $authfields = [
            'mainuserid' => 'privacy:metadata:auth_companion:mainuserid',
            'companionid' => 'privacy:metadata:auth_companion:companionid',
            'timecreated' => 'privacy:metadata:auth_companion:timecreated',
        ];

        $collection->add_database_table('auth_companion_accounts', $authfields, 'privacy:metadata:auth_companion:tableexplanation');
        $collection->link_subsystem('core_auth', 'privacy:metadata:auth_companion:authsubsystem');

        return $collection;
    }

    /**
     * Delete user data for the user.
     *
     * @param  int $userid The id of the user.
     */
    protected static function delete_user_data(int $userid) {
        global $DB;

        // Delete the related companion account.
        \auth_companion\util::delete_companionuser($userid, false);
    }
}
