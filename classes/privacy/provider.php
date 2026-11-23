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

namespace message_ntfy\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;

/**
 * Privacy provider: the user's topic, and what is sent to the ntfy server.
 *
 * @package    message_ntfy
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\user_preference_provider {
    #[\Override]
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference('message_processor_ntfy_topic', 'privacy:metadata:preference:topic');
        $collection->add_external_location_link('ntfy', [
            'topic' => 'privacy:metadata:topic',
            'title' => 'privacy:metadata:title',
            'message' => 'privacy:metadata:message',
            'click' => 'privacy:metadata:click',
        ], 'privacy:metadata:externalpurpose');
        return $collection;
    }

    #[\Override]
    public static function export_user_preferences(int $userid) {
        $topic = get_user_preferences('message_processor_ntfy_topic', null, $userid);
        if ($topic !== null) {
            writer::export_user_preference(
                'message_ntfy',
                'message_processor_ntfy_topic',
                $topic,
                get_string('privacy:metadata:preference:topic', 'message_ntfy')
            );
        }
    }
}
