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

use auth_companion\globals as gl;

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
        $mform      = $this->_form;
        $customdata = $this->_customdata;
        $courseid   = (int) ($customdata['courseid'] ?? 0);

        $this->type           = $customdata['type'] ?? '';
        $this->companionroles = $customdata['companionroles'] ?? [];

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
                $actionbuttonstring = get_string('switch_back', 'auth_companion');
                $this->add_leave_elements($mform);
                break;
            case 'enter':
                $actionbuttonstring = get_string('switch_to_companion', 'auth_companion');
                $this->add_enter_elements($mform);
                break;
            default:
                throw new \moodle_exception('unknown confirmation type');
        }
        $this->add_action_buttons(true, $actionbuttonstring);
    }

    /**
     * Validation of submitted content.
     *
     * @param  array $data  array of ("fieldname"=>value) of submitted data
     * @param  array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *               or an empty array if everything is OK (true allowed for backwards compatibility too)
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

    /**
     * Add the additional elements to the form.
     *
     * @param  \MoodleQuickForm $mform
     * @return void
     */
    protected function add_enter_elements($mform) {
        global $CFG;
        $mycfg = gl::mycfg();

        $mform->addElement('select', 'companionrole', get_string('companionrole', 'auth_companion'), $this->companionroles);

        $notice = '';
        switch ($mycfg->emailoverride) {
            case gl::EMAILFORCEOVERRIDE:
                $notice = get_string('switch_to_companion_note_email_override_force', 'auth_companion');
                $mform->addElement('hidden', 'emailoverride', 1);
                $mform->setType('emailoverride', PARAM_BOOL);
                $mform->setConstant('emailoverride', 1);
                break;
            case gl::EMAILOPTIONALOVERRIDE:
                $notice = get_string('switch_to_companion_note_email_override_optional', 'auth_companion');
                $mform->addElement('checkbox', 'emailoverride', get_string('override_email', 'auth_companion'));
                break;
            case gl::EMAILNOOVERRIDE:
            default:
                $mform->addElement('hidden', 'emailoverride', 0);
                $mform->setType('emailoverride', PARAM_BOOL);
                $mform->setConstant('emailoverride', 0);
                $notice = get_string('switch_to_companion_note_email_override_no', 'auth_companion');
        }

        $mform->addElement(
            'static',
            'static1',
            get_string('notice'),
            $notice
        );
    }

    /**
     * Add the additional elements to the form.
     *
     * @param  \MoodleQuickForm $mform
     * @return void
     */
    protected function add_leave_elements($mform) {
        global $CFG;
        $mycfg = gl::mycfg();

        if (!empty($mycfg->forcedeletedata)) {
            $mform->addElement('hidden', 'deletedata', 1);
            $mform->setType('deletedata', PARAM_BOOL);
            $mform->setConstant('deletedata', 1);
        } else {
            $mform->addElement('checkbox', 'deletedata', get_string('delete_data', 'auth_companion'));
        }
    }
}
