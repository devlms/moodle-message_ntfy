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
 * ntfy message processor.
 *
 * @package    message_ntfy
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/message/output/lib.php');

/**
 * Sends Moodle notifications to a user's ntfy topic (https://docs.ntfy.sh/publish/).
 *
 * @package    message_ntfy
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_output_ntfy extends message_output {
    /** @var string The user preference that holds the topic. */
    public const PREFERENCE = 'message_processor_ntfy_topic';

    /** @var string Topic rule from the ntfy docs: letters, numbers, - and _, up to 64. */
    public const TOPIC_PATTERN = '/^[-_A-Za-z0-9]{1,64}$/';

    /** @var int ntfy's message size limit in bytes; larger bodies become attachments. */
    public const MAX_BYTES = 4096;

    #[\Override]
    public function send_message($message) {
        $user = is_object($message->userto) ? $message->userto : core_user::get_user($message->userto);
        if (empty($user) || !$this->is_user_configured($user)) {
            return false;
        }

        $server = rtrim(trim((string)get_config('message_ntfy', 'server')) ?: 'https://ntfy.sh', '/') . '/';
        $headers = ['Content-Type' => 'application/json'];
        $token = trim((string)get_config('message_ntfy', 'token'));
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        try {
            $response = \core\di::get(\core\http_client::class)->post($server, [
                'headers' => $headers,
                'body' => json_encode($this->payload($message, $this->topic($user)), JSON_UNESCAPED_SLASHES),
                'timeout' => 10,
                'http_errors' => false,
            ]);
        } catch (\Throwable $e) {
            debugging('ntfy: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

        $answer = json_decode((string)$response->getBody(), true);
        if ($response->getStatusCode() !== 200 || empty($answer['id'])) {
            debugging('ntfy: the server answered ' . $response->getStatusCode() . ' without a message id', DEBUG_DEVELOPER);
            return false;
        }
        return true;
    }

    /**
     * The JSON body for one notification.
     *
     * @param stdClass $message the message from core
     * @param string $topic the user's topic
     * @return array
     */
    public function payload(stdClass $message, string $topic): array {
        $text = trim((string)($message->fullmessage ?? ''));
        if ($text === '' && !empty($message->fullmessagehtml)) {
            $text = trim(html_to_text($message->fullmessagehtml, 0, false));
        }
        if ($text === '') {
            $text = trim((string)($message->smallmessage ?? ''));
        }

        $payload = [
            'topic' => $topic,
            'title' => core_text::str_max_bytes((string)($message->subject ?? ''), 250),
            'message' => core_text::str_max_bytes($text, self::MAX_BYTES - 96),
        ];
        if (!empty($message->contexturl)) {
            $payload['click'] = (string)$message->contexturl;
        }
        return $payload;
    }

    /**
     * The user's saved topic, or ''.
     *
     * @param stdClass $user
     * @return string
     */
    protected function topic(stdClass $user): string {
        return (string)get_user_preferences(self::PREFERENCE, '', $user->id);
    }

    #[\Override]
    public function is_user_configured($user = null) {
        global $USER;
        $user = $user ?? $USER;
        if (empty($user->id) || !empty($user->deleted) || !empty($user->suspended) || ($user->auth ?? '') === 'nologin') {
            return false;
        }
        return preg_match(self::TOPIC_PATTERN, $this->topic($user)) === 1;
    }

    #[\Override]
    public function load_data(&$preferences, $userid) {
        $preferences->ntfy_topic = get_user_preferences(self::PREFERENCE, '', $userid);
    }

    #[\Override]
    public function config_form($preferences) {
        $suggestion = 'moodle-' . strtolower(random_string(12));
        $current = $preferences->ntfy_topic ?? '';
        return html_writer::label(get_string('topic', 'message_ntfy'), 'ntfy_topic') .
            html_writer::empty_tag('input', [
                'type' => 'text',
                'id' => 'ntfy_topic',
                'name' => 'ntfy_topic',
                'value' => $current,
                'size' => 30,
                'maxlength' => 64,
                'pattern' => '[-_A-Za-z0-9]{1,64}',
                'placeholder' => $suggestion,
                'class' => 'form-control',
                'aria-describedby' => 'ntfy_topic_help',
            ]) .
            html_writer::div(
                get_string('topic_help', 'message_ntfy', $suggestion),
                'form-text text-muted',
                ['id' => 'ntfy_topic_help']
            );
    }

    #[\Override]
    public function process_form($form, &$preferences) {
        if (!isset($form->ntfy_topic)) {
            return;
        }
        $topic = trim((string)$form->ntfy_topic);
        if ($topic === '') {
            $preferences[self::PREFERENCE] = null;
        } else if (preg_match(self::TOPIC_PATTERN, $topic)) {
            $preferences[self::PREFERENCE] = $topic;
        }
    }

    #[\Override]
    public function get_default_messaging_settings() {
        return MESSAGE_PERMITTED;
    }
}
