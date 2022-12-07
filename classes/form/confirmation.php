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

namespace auth_companion\form;
use \auth_companion\globals as gl;

/**
 * Confirmation form.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confirmation extends base {

    /** @var string The values can be 'enter' or 'leave'. */
    protected $type;
    /** @var \array */
    protected $companionroles;

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {

        $mform = $this->_form;
        $customdata = $this->_customdata;
        $courseid = (int) ($customdata['courseid'] ?? 0);

        $this->type = $customdata['type'] ?? '';
        $this->companionroles = $customdata['companionroles'] ?? array();

        $backurl = $customdata['backurl'] ?? '/';
        $backurl = new \moodle_url($backurl);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setConstant('courseid', $courseid);

        $mform->addElement('hidden', 'backurl');
        $mform->setType('backurl', PARAM_LOCALURL);
        $mform->setConstant('backurl', $backurl);

        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_BOOL);
        $mform->setConstant('confirm', true);

        switch ($this->type) {
            case 'leave':
                $mform->addElement('checkbox', 'deletedata', get_string('delete_data', 'auth_companion'));
                $actionbuttonstring = get_string('switch_back', 'auth_companion');
                break;
            case 'enter':
                $mform->addElement('select', 'companionrole', get_string('companionrole', 'auth_companion'), $this->companionroles);
                $actionbuttonstring = get_string('switch_to_companion', 'auth_companion');
                break;
            default:
                throw new \moodle_exception('unknown confirmation type');
        }
        $this->add_action_buttons(true, $actionbuttonstring);
    }

    /**
     * Validation of submitted content.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $data = (object) $data;

        if ($this->type == 'enter') {
            if (empty($this->companionroles) || !in_array($data->companionrole, array_keys($this->companionroles))) {
                $errors['companionrole'] = get_string('wrong_or_missing_role', 'auth_companion');
            }
        }
        return $errors;
    }
}
