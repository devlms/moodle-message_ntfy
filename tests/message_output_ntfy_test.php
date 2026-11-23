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

namespace message_ntfy;

use GuzzleHttp\Psr7\Response;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/message/output/ntfy/message_output_ntfy.php');

/**
 * Tests for the ntfy message output.
 *
 * @package    message_ntfy
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \message_output_ntfy
 */
final class message_output_ntfy_test extends \advanced_testcase {
    /**
     * A message like the ones core hands to processors.
     *
     * @param \stdClass $user recipient
     * @return \stdClass
     */
    private function message(\stdClass $user): \stdClass {
        return (object)[
            'userto' => $user,
            'userfrom' => \core_user::get_noreply_user(),
            'component' => 'mod_assign',
            'name' => 'assign_notification',
            'subject' => 'Essay 2 has been graded',
            'fullmessage' => 'Your essay got 14/20. Feedback is in the assignment.',
            'fullmessageformat' => FORMAT_PLAIN,
            'fullmessagehtml' => '<p>Your essay got 14/20.</p>',
            'smallmessage' => 'Essay 2 graded',
            'contexturl' => 'https://school.example/mod/assign/view.php?id=7',
            'notification' => 1,
        ];
    }

    /**
     * A user with a topic.
     *
     * @param string $topic
     * @return \stdClass
     */
    private function user(string $topic = 'moodle-ama-8f3k2q'): \stdClass {
        $user = $this->getDataGenerator()->create_user();
        set_user_preference('message_processor_ntfy_topic', $topic, $user);
        return $user;
    }

    public function test_sends_json_to_the_server_root(): void {
        $this->resetAfterTest();
        $history = [];
        ['mock' => $mock] = $this->get_mocked_http_client($history);
        $mock->append(new Response(200, ['Content-Type' => 'application/json'], '{"id":"xE73Iyuabi","event":"message"}'));

        $this->assertTrue((new \message_output_ntfy())->send_message($this->message($this->user())));

        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://ntfy.sh/', (string)$request->getUri());
        $this->assertSame('', $request->getHeaderLine('Authorization'), 'No token, no header');
        $body = json_decode((string)$request->getBody(), true);
        $this->assertSame('moodle-ama-8f3k2q', $body['topic']);
        $this->assertSame('Essay 2 has been graded', $body['title']);
        $this->assertSame('Your essay got 14/20. Feedback is in the assignment.', $body['message']);
        $this->assertSame('https://school.example/mod/assign/view.php?id=7', $body['click']);
    }

    public function test_own_server_and_access_token(): void {
        $this->resetAfterTest();
        set_config('server', 'https://push.school.example/', 'message_ntfy');
        set_config('token', 'tk_AgQdq7mVBoFD37zQVN29RhuMzNIz2', 'message_ntfy');
        $history = [];
        ['mock' => $mock] = $this->get_mocked_http_client($history);
        $mock->append(new Response(200, [], '{"id":"a1"}'));

        $this->assertTrue((new \message_output_ntfy())->send_message($this->message($this->user())));
        $request = $history[0]['request'];
        $this->assertSame('https://push.school.example/', (string)$request->getUri());
        $this->assertSame('Bearer tk_AgQdq7mVBoFD37zQVN29RhuMzNIz2', $request->getHeaderLine('Authorization'));
    }

    public function test_user_without_topic_is_skipped(): void {
        $this->resetAfterTest();
        $history = [];
        $this->get_mocked_http_client($history);
        $user = $this->getDataGenerator()->create_user();
        $output = new \message_output_ntfy();
        $this->assertFalse($output->is_user_configured($user));
        $this->assertFalse($output->send_message($this->message($user)));
        $this->assertCount(0, $history);
    }

    public function test_suspended_user_is_skipped(): void {
        $this->resetAfterTest();
        $user = $this->user();
        $user->suspended = 1;
        $this->assertFalse((new \message_output_ntfy())->is_user_configured($user));
    }

    public function test_server_error_is_a_failure(): void {
        $this->resetAfterTest();
        ['mock' => $mock] = $this->get_mocked_http_client();
        $mock->append(new Response(403, [], '{"code":40301,"http":403,"error":"forbidden"}'));
        $this->assertFalse((new \message_output_ntfy())->send_message($this->message($this->user())));
        $this->assertDebuggingCalled();
    }

    public function test_answer_that_is_not_ntfy_is_a_failure(): void {
        $this->resetAfterTest();
        ['mock' => $mock] = $this->get_mocked_http_client();
        $mock->append(new Response(200, ['Content-Type' => 'text/html'], '<html>Sign in</html>'));
        $this->assertFalse((new \message_output_ntfy())->send_message($this->message($this->user())));
        $this->assertDebuggingCalled();
    }

    public function test_long_text_is_cut_to_the_ntfy_limit(): void {
        $this->resetAfterTest();
        $history = [];
        ['mock' => $mock] = $this->get_mocked_http_client($history);
        $mock->append(new Response(200, [], '{"id":"a1"}'));
        $message = $this->message($this->user());
        $message->fullmessage = str_repeat('é', 3000);

        (new \message_output_ntfy())->send_message($message);
        $body = json_decode((string)$history[0]['request']->getBody(), true);
        $this->assertLessThanOrEqual(4096, strlen($body['message']));
        $this->assertTrue(mb_check_encoding($body['message'], 'UTF-8'), 'Not cut inside a character');
    }

    public function test_html_only_message_is_sent_as_text(): void {
        $this->resetAfterTest();
        $history = [];
        ['mock' => $mock] = $this->get_mocked_http_client($history);
        $mock->append(new Response(200, [], '{"id":"a1"}'));
        $message = $this->message($this->user());
        $message->fullmessage = '';
        $message->contexturl = '';

        (new \message_output_ntfy())->send_message($message);
        $body = json_decode((string)$history[0]['request']->getBody(), true);
        $this->assertSame('Your essay got 14/20.', trim($body['message']));
        $this->assertArrayNotHasKey('click', $body);
    }

    public function test_form_saves_a_valid_topic_and_rejects_others(): void {
        $output = new \message_output_ntfy();
        $preferences = [];
        $output->process_form((object)['ntfy_topic' => ' my-phone_42 '], $preferences);
        $this->assertSame('my-phone_42', $preferences['message_processor_ntfy_topic']);

        $preferences = [];
        $output->process_form((object)['ntfy_topic' => 'has spaces/and slashes'], $preferences);
        $this->assertArrayNotHasKey('message_processor_ntfy_topic', $preferences);

        $preferences = [];
        $output->process_form((object)['ntfy_topic' => ''], $preferences);
        $this->assertArrayHasKey('message_processor_ntfy_topic', $preferences);
        $this->assertNull($preferences['message_processor_ntfy_topic'], 'Empty removes the topic');
    }

    public function test_message_send_reaches_ntfy(): void {
        $this->preventResetByRollback();
        $this->resetAfterTest();
        \core\plugininfo\message::enable_plugin('ntfy', 1);
        set_config('message_provider_mod_assign_assign_notification_enabled', 'ntfy', 'message');
        $history = [];
        ['mock' => $mock] = $this->get_mocked_http_client($history);
        $mock->append(new Response(200, [], '{"id":"a1"}'));

        $message = new \core\message\message();
        foreach ((array)$this->message($this->user()) as $key => $value) {
            $message->$key = $value;
        }
        message_send($message);

        $this->assertCount(1, $history);
        $this->assertSame('Essay 2 has been graded', json_decode((string)$history[0]['request']->getBody(), true)['title']);
    }
}
