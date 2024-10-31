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

/**
 * A setting based on \admin_setting_configtext but only for a domain.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adminsetting_domain extends \admin_setting_configtext {

    /**
     * Config text constructor
     *
     * @param string $name unique name for this setting.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param string $defaultsetting
     * @param int $size default field size
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $size=null) {
        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_RAW, $size);
    }

    /**
     * Validate data before storage
     * @param string $data
     * @return mixed true if ok string if error found
     */
    public function validate($data) {
        if (empty($data)) {
            return get_string('error_empty_emaildomain', 'auth_companion');
        }
        $dummyaddress = 'dummy@' . $data;
        if (validate_email($dummyaddress)) {
            return true;
        }
        return get_string('error_wrong_emaildomain', 'auth_companion');
    }
}
