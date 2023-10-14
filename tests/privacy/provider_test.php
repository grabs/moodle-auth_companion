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

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

/**
 * Test for the auth_companion privacy provider.
 *
 * @package    auth_companion
 * @copyright  2021 Jake Dallimore <jrhdallimore@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \auth_companion\privacy\provider
 */
class provider_test extends provider_testcase {
    /**
     * Set up method.
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Check that a user context is returned if there is any user data for this user.
     *
     * @covers ::get_contexts_for_userid
     */
    public function test_get_contexts_for_userid() {
        $user = $this->getDataGenerator()->create_user();
        $this->assertEmpty(provider::get_contexts_for_userid($user->id));

        $companion   = new \auth_companion\companion($user);
        $contextlist = provider::get_contexts_for_userid($user->id);
        // Check that we only get back one context.
        $this->assertCount(1, $contextlist);

        // Check that a context is returned is the expected.
        $usercontext = \context_user::instance($user->id);
        $this->assertEquals($usercontext->id, $contextlist->get_contextids()[0]);
    }

    /**
     * Test that user data is exported correctly.
     *
     * @covers ::export_user_data
     */
    public function test_export_user_data() {
        $user = $this->getDataGenerator()->create_user();

        $companion   = new \auth_companion\companion($user);
        $contextlist = provider::get_contexts_for_userid($user->id);
        $usercontext = \context_user::instance($user->id);

        $writer = writer::with_context($usercontext);
        $this->assertFalse($writer->has_any_data());
        $approvedlist = new approved_contextlist($user, 'auth_companion', [$usercontext->id]);
        provider::export_user_data($approvedlist);
        $data = (object) $writer->get_data([
            get_string('privacy:metadata:auth_companion', 'auth_companion'),
            $companion->get_companion_id(),
        ]);
        $this->assertEquals($companion->get_companion_id(), $data->companionid);
    }

    /**
     * Test deleting all user data for a specific context.
     *
     * @covers ::delete_data_for_all_users_in_context
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        $user1        = $this->getDataGenerator()->create_user();
        $companion1   = new \auth_companion\companion($user1);
        $user1context = \context_user::instance($user1->id);

        $user2        = $this->getDataGenerator()->create_user();
        $companion2   = new \auth_companion\companion($user2);
        $user2context = \context_user::instance($user2->id);

        // Verify there are two linked logins.
        $companionaccounts = $DB->get_records('auth_companion_accounts');
        $this->assertCount(2, $companionaccounts);

        // Delete everything for the first user context.
        provider::delete_data_for_all_users_in_context($user1context);

        // Get all LTI linked accounts match with user1.
        $companionaccounts = $DB->get_records('auth_companion_accounts', ['mainuserid' => $user1->id]);
        $this->assertCount(0, $companionaccounts);

        // Verify there is now only one linked login.
        $companionaccounts = $DB->get_records('auth_companion_accounts');
        $this->assertCount(1, $companionaccounts);
    }

    /**
     * This should work identical to the above test.
     *
     * @covers ::delete_data_for_user
     */
    public function test_delete_data_for_user() {
        global $DB;
        $user1        = $this->getDataGenerator()->create_user();
        $companion1   = new \auth_companion\companion($user1);
        $user1context = \context_user::instance($user1->id);

        $user2        = $this->getDataGenerator()->create_user();
        $companion2   = new \auth_companion\companion($user2);
        $user2context = \context_user::instance($user2->id);

        // Verify there are two linked logins.
        $ltiaccounts = $DB->get_records('auth_companion_accounts');
        $this->assertCount(2, $ltiaccounts);

        // Delete everything for the first user.
        $approvedlist = new approved_contextlist($user1, 'auth_companion', [$user1context->id]);
        provider::delete_data_for_user($approvedlist);

        // Get all LTI accounts linked with user1.
        $ltiaccounts = $DB->get_records('auth_companion_accounts', ['mainuserid' => $user1->id]);
        $this->assertCount(0, $ltiaccounts);

        // Verify there is only one linked login now.
        $ltiaccounts = $DB->get_records('auth_companion_accounts', []);
        $this->assertCount(1, $ltiaccounts);
    }

    /**
     * Test that only users with a user context are fetched.
     *
     * @covers ::get_users_in_context
     */
    public function test_get_users_in_context() {
        $user        = $this->getDataGenerator()->create_user();
        $contextlist = provider::get_contexts_for_userid($user->id);
        $usercontext = \context_user::instance($user->id);
        $component   = 'auth_companion';

        // The list of users should not return anything yet (no linked login yet).
        $userlist = new userlist($usercontext, $component);
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);

        $companion = new \auth_companion\companion($user);

        // The list of users for user context should return the user.
        provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
        $expected = [$user->id];
        $actual   = $userlist->get_userids();
        $this->assertEquals($expected, $actual);

        // The list of users for system context should not return any users.
        $systemcontext = \context_system::instance();
        $userlist      = new userlist($systemcontext, $component);
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);
    }

    /**
     * Test that data for users in approved userlist is deleted.
     *
     * @covers ::delete_data_for_users
     */
    public function test_delete_data_for_users() {
        $component    = 'auth_companion';
        $user1        = $this->getDataGenerator()->create_user();
        $usercontext1 = \context_user::instance($user1->id);
        $user2        = $this->getDataGenerator()->create_user();
        $usercontext2 = \context_user::instance($user2->id);

        $companion1 = new \auth_companion\companion($user1);
        $companion2 = new \auth_companion\companion($user2);

        // The list of users for usercontext1 should return user1.
        $userlist1 = new userlist($usercontext1, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(1, $userlist1);
        $expected = [$user1->id];
        $actual   = $userlist1->get_userids();
        $this->assertEquals($expected, $actual);

        // The list of users for usercontext2 should return user2.
        $userlist2 = new userlist($usercontext2, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);
        $expected = [$user2->id];
        $actual   = $userlist2->get_userids();
        $this->assertEquals($expected, $actual);

        // Add userlist1 to the approved user list.
        $approvedlist = new approved_userlist($usercontext1, $component, $userlist1->get_userids());

        // Delete user data using delete_data_for_user for usercontext1.
        provider::delete_data_for_users($approvedlist);

        // Re-fetch users in usercontext1 - The user list should now be empty.
        $userlist1 = new userlist($usercontext1, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(0, $userlist1);
        // Re-fetch users in usercontext2 - The user list should not be empty (user2).
        $userlist2 = new userlist($usercontext2, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);

        // User data should be only removed in the user context.
        $systemcontext = \context_system::instance();
        // Add userlist2 to the approved user list in the system context.
        $approvedlist = new approved_userlist($systemcontext, $component, $userlist2->get_userids());
        // Delete user1 data using delete_data_for_user.
        provider::delete_data_for_users($approvedlist);
        // Re-fetch users in usercontext2 - The user list should not be empty (user2).
        $userlist2 = new userlist($usercontext2, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);
    }
}
