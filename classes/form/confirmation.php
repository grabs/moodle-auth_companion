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

namespace auth_companion\form;
use \auth_companion\globals as gl;

/**
 * Confirmation form.
 *
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confirmation extends base {

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        $CFG = gl::cfg();
        $OUTPUT = gl::output();

        $mform = $this->_form;
        $customdata = $this->_customdata;
        $courseid = (int) empty($customdata['courseid']) ? 0 : $customdata['courseid'];

        $backurl = empty($customdata['backurl']) ? '/' : $customdata['backurl'];
        $backurl = new \moodle_url($backurl);

        $type = $customdata['type'];

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setConstant('courseid', $courseid);

        $mform->addElement('hidden', 'backurl');
        $mform->setType('backurl', PARAM_LOCALURL);
        $mform->setConstant('backurl', $backurl);

        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_BOOL);
        $mform->setConstant('confirm', true);

        if ($type == 'leave') {
            $mform->addElement('checkbox', 'deletedata', get_string('delete_data', 'auth_companion'));
        }

        if ($type == 'leave') {
            $this->add_action_buttons(true, get_string('switch_back', 'auth_companion'));
        } else if ($type == 'enter') {
            $this->add_action_buttons(true, get_string('switch_to_companion', 'auth_companion'));
        } else {
            throw new \moodle_exception('unknown confirmation type');
        }
    }
}
