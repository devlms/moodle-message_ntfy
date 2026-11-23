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
 * Registers the ntfy message processor.
 *
 * @package    message_ntfy
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add the processor record, disabled until an administrator turns it on.
 *
 * @return bool
 */
function xmldb_message_ntfy_install(): bool {
    global $DB;
    $DB->insert_record('message_processors', (object)['name' => 'ntfy']);
    $class = \core_plugin_manager::resolve_plugininfo_class('message');
    $class::enable_plugin('ntfy', 0);
    return true;
}
