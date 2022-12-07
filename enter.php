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

use \auth_companion\globals as gl;

require_once(dirname(dirname(__DIR__)).'/config.php');

$courseid = required_param('courseid', PARAM_INT);
$DB = gl::db();

// TODO: Do a proper check of capabilities and so on.

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('course not found');
}

$context = \context_system::instance();
$coursecontext = \context_course::instance($course->id);
require_capability('auth/companion:allowcompanion', $context);
$pagetitle = get_string('pluginname', 'auth_companion');
$title = get_string('switch_to_companion', 'auth_companion');
$text = get_string('switch_to_companion_text', 'auth_companion');

$myurl = new \moodle_url($FULLME);
$myurl->remove_all_params();
$myurl->param('courseid', $courseid);

/** @var \moodle_page $PAGE */
$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('frontpage');
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);

$confirmform = new \auth_companion\form\confirmation(null, array('courseid' => $course->id, 'type' => 'enter'));
if ($confirmform->is_cancelled()) {
    redirect(new \moodle_url('/course/view.php', array('id' => $course->id)));
}
if ($data = $confirmform->get_data()) {
    $olduserid = $USER->id;
    $companion = new \auth_companion\companion($USER, true);

    $user = $companion->login();
    // Now you are logged in as companion.

    set_user_preference('auth_companion_course', $courseid);
    set_user_preference('auth_companion_mainuser', $olduserid);
    $redirecturl = new \moodle_url('/course/view.php', array('id' => $course->id));
    $companion->enrol($course);
    $notificationtext = get_string('info_using_companion', 'auth_companion', fullname($user));
    \core\notification::add($notificationtext, \core\notification::SUCCESS);
    redirect($redirecturl);
}

$confirmwidget = new \auth_companion\output\confirm($confirmform, $title, $text);

echo $OUTPUT->header();
echo $OUTPUT->render($confirmwidget);
echo $OUTPUT->footer();
