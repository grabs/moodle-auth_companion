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
 * Provide all globals we need.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class globals {
    /** @var this auth type */
    public const AUTH = 'companion';

    /**
     * Returns the \moodle_database instance
     *
     * @return \moodle_database
     */
    public static function db() {
        global $DB;
        return $DB;
    }

    /**
     * Returns the \renderer_base instance
     *
     * @return \renderer_base
     */
    public static function output() {
        global $OUTPUT;
        return $OUTPUT;
    }

    /**
     * Returns the $SESSION instance
     *
     * @return \stdClass
     */
    public static function session() {
        global $SESSION;
        return $SESSION;
    }

    /**
     * Returns the $USER instance
     *
     * @return \stdClass
     */
    public static function user() {
        global $USER;
        return $USER;
    }

    /**
     * Returns the $PAGE instance
     *
     * @return \moodle_page
     */
    public static function page() {
        global $PAGE;
        return $PAGE;
    }

    /**
     * Returns the $CFG instance
     *
     * @return \stdClass
     */
    public static function cfg() {
        global $CFG;
        return $CFG;
    }

    /**
     * Returns the plugin configuration.
     *
     * @return \stdClass
     */
    public static function mycfg() {
        static $mycfg;

        if (empty($mycfg)) {
            $mycfg = get_config('auth_companion');
        }

        return $mycfg;
    }

    /**
     * Returns the $SITE instance
     *
     * @return \stdClass
     */
    public static function site() {
        global $SITE;
        return $SITE;
    }

    /**
     * Returns the $COURSE instance
     *
     * @return \stdClass
     */
    public static function course() {
        global $COURSE;
        return $COURSE;
    }

    /**
     * Returns the $FULLME instance
     *
     * @return string
     */
    public static function fullme() {
        global $FULLME;
        return $FULLME;
    }
}
