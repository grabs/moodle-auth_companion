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
 * Unit tests for companion account features.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class companion_test extends \advanced_testcase {
    /**
     * Test create a companion account.
     *
     * @covers \auth_companion\companion::__construct
     * @return void
     */
    public function test_create_companion_account_with_all_groups(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        // Prepare course.
        $course      = $this->getDataGenerator()->create_course();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);

        // Prepare groups.
        $group1 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $group2 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $groups = groups_get_all_groups($course->id);
        $this->assertEquals(2, count($groups));

        // There should be no member in any group.
        $groupmembers = groups_get_members($group1->id);
        $this->assertEquals(0, count($groupmembers));
        $groupmembers = groups_get_members($group2->id);
        $this->assertEquals(0, count($groupmembers));

        // Add the current user to the groups.
        $this->getDataGenerator()->enrol_user($USER->id, $course->id);
        groups_add_member($group1->id, $USER->id);
        groups_add_member($group2->id, $USER->id);

        // Check the user is in that groups.
        $groupmembers = groups_get_members($group1->id);
        $this->assertEquals(1, count($groupmembers));
        $groupmembers = groups_get_members($group2->id);
        $this->assertEquals(1, count($groupmembers));

        // Check the count of users and companions before.
        $countuserbefore      = $DB->count_records('user', ['deleted' => 0, 'auth' => gl::AUTH]);
        $countcompanionbefore = $DB->count_records('auth_companion_accounts', null);

        $companion = new \auth_companion\companion($USER);

        // Check the count of users and companions after.
        $countuserafter      = $DB->count_records('user', ['deleted' => 0, 'auth' => gl::AUTH]);
        $countcompanionafter = $DB->count_records('auth_companion_accounts', null);

        // Now there should be more counts after than before.
        $this->assertTrue($countuserbefore < $countuserafter);
        $this->assertTrue($countcompanionbefore < $countcompanionafter);

        // We should be able to get an id.
        $companionid = $companion->get_companion_id();
        $this->assertNotEmpty($companionid);

        // We should be able to get a companion instance by using the companion id.
        $companionagain = \auth_companion\companion::get_instance_by_companion($companionid);
        $this->assertEquals($companionid, $companionagain->get_companion_id());

        // Enrol the companion user into the prepared course and join him to all its groups.
        $companion->enrol($course, $studentrole->id, gl::MYGROUPS);
        $this->assertEquals(2, $DB->count_records('user_enrolments', null));
        $this->assertEquals(2, $DB->count_records('role_assignments', null));
        $groupmembers = groups_get_members($group1->id);
        $this->assertEquals(2, count($groupmembers));
        $groupmembers = groups_get_members($group2->id);
        $this->assertEquals(2, count($groupmembers));
    }

    /**
     * Test create a companion account.
     *
     * @covers \auth_companion\companion::__construct
     * @return void
     */
    public function test_create_companion_account_with_single_group(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        // Prepare course.
        $course      = $this->getDataGenerator()->create_course();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);

        // Prepare groups.
        $group1 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $group2 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $groups = groups_get_all_groups($course->id);
        $this->assertEquals(2, count($groups));

        // There should be no member in any group.
        $groupmembers = groups_get_members($group1->id);
        $this->assertEquals(0, count($groupmembers));
        $groupmembers = groups_get_members($group2->id);
        $this->assertEquals(0, count($groupmembers));

        // Add the current user to the groups.
        $this->getDataGenerator()->enrol_user($USER->id, $course->id);
        groups_add_member($group1->id, $USER->id);
        groups_add_member($group2->id, $USER->id);

        // Check the user is in that groups.
        $groupmembers = groups_get_members($group1->id);
        $this->assertEquals(1, count($groupmembers));
        $groupmembers = groups_get_members($group2->id);
        $this->assertEquals(1, count($groupmembers));

        // Check the count of users and companions before.
        $countuserbefore      = $DB->count_records('user', ['deleted' => 0, 'auth' => gl::AUTH]);
        $countcompanionbefore = $DB->count_records('auth_companion_accounts', null);

        $companion = new \auth_companion\companion($USER);

        // Check the count of users and companions after.
        $countuserafter      = $DB->count_records('user', ['deleted' => 0, 'auth' => gl::AUTH]);
        $countcompanionafter = $DB->count_records('auth_companion_accounts', null);

        // Now there should be more counts after than before.
        $this->assertTrue($countuserbefore < $countuserafter);
        $this->assertTrue($countcompanionbefore < $countcompanionafter);

        // We should be able to get an id.
        $companionid = $companion->get_companion_id();
        $this->assertNotEmpty($companionid);

        // We should be able to get a companion instance by using the companion id.
        $companionagain = \auth_companion\companion::get_instance_by_companion($companionid);
        $this->assertEquals($companionid, $companionagain->get_companion_id());

        // Enrol the companion user into the prepared course and join him to group1.
        $companion->enrol($course, $studentrole->id, $group1->id);
        $this->assertEquals(2, $DB->count_records('user_enrolments', null));
        $this->assertEquals(2, $DB->count_records('role_assignments', null));
        $groupmembers = groups_get_members($group1->id);
        $this->assertEquals(2, count($groupmembers));
        $groupmembers = groups_get_members($group2->id);
        $this->assertEquals(1, count($groupmembers));
    }

    /**
     * Test delete a companion account.
     *
     * @covers \auth_companion\util::delete_companionuser
     * @return void
     */
    public function test_delete_companion_account(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        $companion   = new \auth_companion\companion($USER);
        $companionid = $companion->get_companion_id();

        // Check the count of users and companions before deleting.
        $countuserbefore      = $DB->count_records('user', ['deleted' => 0, 'auth' => gl::AUTH]);
        $countcompanionbefore = $DB->count_records('auth_companion_accounts', null);

        \auth_companion\util::delete_companionuser($companion->get_companion_id());

        // Check the count of users and companions after deleting.
        $countuserafter      = $DB->count_records('user', ['deleted' => 0, 'auth' => gl::AUTH]);
        $countcompanionafter = $DB->count_records('auth_companion_accounts', null);

        // We should now have fewer users and companions.
        $this->assertTrue($countuserbefore > $countuserafter);
        $this->assertTrue($countcompanionbefore > $countcompanionafter);

        // We should not be able to get an instance anymore.
        try {
            $companion = \auth_companion\companion::get_instance_by_companion($companionid);
        } catch (\Throwable $e) {
            $thrown = true; // This error is expected.
        }
        $this->assertNotEmpty($thrown);
    }
}
