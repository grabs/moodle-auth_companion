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

namespace auth_companion\output;

/**
 * Output component to render a notification.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class domainsettingsnote implements \renderable, \templatable {
    /** @var array */
    protected $data = [];

    /**
     * Constructor.
     *
     * @param \stdClass $config The plugin configuration
     */
    public function __construct(\stdClass $config) {
        switch (true) {
            case empty($config->emaildomain):
                $this->data['errmsg'] = get_string('error_empty_emaildomain', 'auth_companion') .
                                    ' - ' . get_string('info_plugin_remains_deactivated', 'auth_companion');
                break;
            case !\auth_companion\util::validate_emaildomain($config->emaildomain):
                $this->data['errmsg'] = get_string('error_wrong_emaildomain', 'auth_companion') .
                                    ' - ' . get_string('info_plugin_remains_deactivated', 'auth_companion');;
                break;
        }
    }

    /**
     * Get the mustache context data.
     *
     * @param  \renderer_base  $output
     * @return \stdClass|array
     */
    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }
}
