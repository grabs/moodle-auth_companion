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
 * Switch back to the main acount.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_companion\globals as gl;

require_once(dirname(__DIR__, 2) . '/config.php');

require_login();

$backurl = optional_param('backurl', '', PARAM_LOCALURL);
if (empty($backurl)) {
    $backurl = new \moodle_url('/');
} else {
    $backurl = new \moodle_url($backurl);
}

$pagetitle       = get_string('pluginname', 'auth_companion');
$title           = get_string('switch_back', 'auth_companion');
$text            = get_string('switch_back_text', 'auth_companion');
$companionuserid = $USER->id;

$context = \context_system::instance();

$myurl = new \moodle_url($FULLME);
$myurl->remove_all_params();
$myurl->param('backurl', $backurl->out());

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('frontpage');
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);

$customdata = [
    'backurl' => $backurl,
    'type'    => 'leave',
];
$confirmform = new \auth_companion\form\confirmation(null, $customdata);
if ($confirmform->is_cancelled()) {
    redirect($backurl);
}

if ($data = $confirmform->get_data()) {
    $mycfg = gl::mycfg();
    if (empty($mycfg->forcelogin)) {
        if (!$companion = \auth_companion\companion::get_instance_by_companion($companionuserid)) {
            require_logout();
            redirect($backurl);
        }
        $user = $companion->relogin_main();
    } else {
        require_logout();
    }
    if (!empty($data->deletedata)) {
        \auth_companion\util::delete_companionuser($companionuserid);
    }
    if (!empty($user)) {
        $notificationtext = get_string('info_using_origin', 'auth_companion', fullname($user));
        \core\notification::add($notificationtext, \core\notification::SUCCESS);
    }
    redirect($backurl);
}

$confirmwidget = new \auth_companion\output\confirm($confirmform, $title, $text);

echo $OUTPUT->header();
echo $OUTPUT->render($confirmwidget);
echo $OUTPUT->footer();
