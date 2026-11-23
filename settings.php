<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Site settings for the ntfy message output.
 *
 * @package    message_ntfy
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'message_ntfy/server',
        get_string('server', 'message_ntfy'),
        get_string('server_desc', 'message_ntfy'),
        'https://ntfy.sh',
        PARAM_URL
    ));
    $settings->add(new admin_setting_configpasswordunmask(
        'message_ntfy/token',
        get_string('token', 'message_ntfy'),
        get_string('token_desc', 'message_ntfy'),
        ''
    ));
}
