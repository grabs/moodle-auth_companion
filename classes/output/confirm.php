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
 * Renderable and templatable component for delete box.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confirm extends base {
    /** @var \auth_companion\form\base */
    private $confirmform;

    /**
     * Constructor.
     *
     * @param \auth_companion\form\base $confirmform
     * @param string                    $title
     * @param string                    $text
     */
    public function __construct($confirmform, $title, $text) {
        parent::__construct();

        $this->confirmform   = $confirmform;
        $this->data['title'] = $title;
        $this->data['text']  = $text;
    }

    /**
     * Data for usage in mustache.
     *
     * @param  \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $this->data['formcontent'] = $this->confirmform->export_for_template($output);

        return $this->data;
    }
}
