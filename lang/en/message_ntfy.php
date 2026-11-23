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
 * Strings for message_ntfy.
 *
 * @package    message_ntfy
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['invalidtopic'] = 'A topic can only use letters, numbers, - and _, up to 64 characters.';
$string['pluginname'] = 'ntfy';
$string['privacy:metadata:click'] = 'The link the notification opens.';
$string['privacy:metadata:externalpurpose'] = 'Notifications are sent to the ntfy server so it can push them to the user\'s phone or computer.';
$string['privacy:metadata:message'] = 'The text of the notification.';
$string['privacy:metadata:preference:topic'] = 'The ntfy topic the user receives notifications on.';
$string['privacy:metadata:title'] = 'The subject of the notification.';
$string['privacy:metadata:topic'] = 'The user\'s ntfy topic.';
$string['server'] = 'ntfy server';
$string['server_desc'] = 'The address of the ntfy server. Leave https://ntfy.sh to use the free public server, or enter your own, for example https://ntfy.school.example. A server on a private network address must also be allowed in the HTTP security settings.';
$string['token'] = 'Access token';
$string['token_desc'] = 'Only for servers that require sign-in to publish. Create a token on the ntfy server (it starts with tk_).';
$string['topic'] = 'Your ntfy topic';
$string['topic_help'] = 'Install the ntfy app on your phone or open the ntfy web app, subscribe to a topic, and enter the same topic here. Anyone who knows the topic can read it, so make it hard to guess, for example {$a}.';
