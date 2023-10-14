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
    /** This auth type */
    public const AUTH = 'companion';

    /** Override constant - Do not override the email address */
    public const EMAILNOOVERRIDE       = 'emailnooverride';
    /** Override constant - Force override the email address */
    public const EMAILFORCEOVERRIDE    = 'emailforceoverride';
    /** Override constant - Optional override the email address */
    public const EMAILOPTIONALOVERRIDE = 'emailoptionaloverride';

    /**
     * Returns the plugin configuration.
     *
     * @return \stdClass
     */
    public static function mycfg() {
        static $mycfg;
        global $CFG;

        if (empty($mycfg)) {
            $mycfg = get_config('auth_companion');
            if (!empty($CFG->authloginviaemail)) {
                $mycfg->emailoverride = static::EMAILNOOVERRIDE;
            }
        }

        return $mycfg;
    }
}
