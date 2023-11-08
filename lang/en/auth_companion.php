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
 * Language definition.
 *
 * @package    auth_companion
 * @copyright  2022 Grabs-EDV (https://www.grabs-edv.com)
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['auth_companiondescription']                        = 'Auth plugin that allows logged in users to use a companion account.';
$string['clean_old_companion_accounts']                     = 'Clean old companion accounts';
$string['companion:allowcompanion']                         = 'Allow companion account';
$string['companion:useascompanion']                         = 'Use this role for companion account';
$string['companionrole']                                    = 'Companion role';
$string['companionrole_definition']                         = 'To define available roles, set the capability "auth/companion:useascompanion" to "Allow".';
$string['delete_data']                                      = 'Delete data';
$string['info_using_companion']                             = 'You are now using your companion account "<strong>{$a}</strong>".';
$string['info_using_origin']                                = 'You are now using your origin account "<strong>{$a}</strong>".';
$string['override_email']                                   = 'Override email address';
$string['pluginname']                                       = 'Companion account';
$string['privacy:metadata']                                 = 'The companion authentication plugin does not store any personal data.';
$string['privacy:metadata:auth_companion']                  = 'Companion account';
$string['privacy:metadata:auth_companion:authsubsystem']    = 'This plugin is connected to the authentication subsystem.';
$string['privacy:metadata:auth_companion:companionid']      = 'The id of the companion user.';
$string['privacy:metadata:auth_companion:mainuserid']       = 'The id of the main user.';
$string['privacy:metadata:auth_companion:tableexplanation'] = 'Companion accounts linked to a user\'s Moodle account.';
$string['privacy:metadata:auth_companion:timecreated']      = 'The timestamp when the companion user account was created.';
$string['setting_email_option_force_override']              = 'Enforce email override';
$string['setting_email_option_help']                        = 'The companion email address can be overridden by the email address of the current user.';
$string['setting_email_option_no_override']                 = 'No email override';
$string['setting_email_option_optional']                    = 'Let the user decide to overwrite the email address.';
$string['setting_email_options']                            = 'Email options';
$string['setting_email_options_help']                       = 'If the setting <strong>$CFG->authloginviaemail</strong> is set, you can not allow email overriding!';
$string['setting_forcelogin']                               = 'Force relogin';
$string['setting_forcelogin_help']                          = 'This setting make sure, the user must relogin to switch to the origin account.';
$string['setting_forcedeletedata']                          = 'Force data deletion';
$string['setting_forcedeletedata_help']                     = 'If this setting is active, the data of the companion user will be deleted when switching back. Otherwise, the user can decide for himself.';
$string['setting_namesuffix']                               = 'Name suffix';
$string['setting_namesuffix_help']                          = 'The value will be used as suffix to your origin name.';
$string['switch_back']                                      = 'Switch back';
$string['switch_back_text']                                 = 'Switch back to your origin account.';
$string['switch_to_companion']                              = 'Switch to companion';
$string['switch_to_companion_text']                         = 'Your current login will be changed to your companion account.';
$string['switch_to_companion_note_email_override_force']    = 'The email address of you current login will be used for your companion account.';
$string['switch_to_companion_note_email_override_no']       = 'A random pseudo email address will be used for your companion account.';
$string['switch_to_companion_note_email_override_optional'] = 'You can choose whether or not your email address will be used for your companion account.';
$string['wrong_or_missing_role']                            = 'Wrong or missing companion role';
