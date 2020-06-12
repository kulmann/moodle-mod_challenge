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

namespace mod_challenge\task;

use coding_exception;
use core\message\message;
use core\task\scheduled_task;
use core_user;
use dml_exception;
use mod_challenge\model\game;
use mod_challenge\model\match;
use mod_challenge\output\challenge_match_invitation;
use mod_challenge\util;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class send_match_invitations
 *
 * @package    mod_challenge\model
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_match_invitations extends scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('task_send_match_invitations', 'mod_challenge');
    }

    /**
     * Execute the task.
     *
     * @throws dml_exception
     */
    public function execute() {
        global $DB;
        $records = $DB->get_records_sql("SELECT * FROM {challenge_matches} WHERE mdl_user_1_notified = 0 OR mdl_user_2_notified = 0", null, 0, 200);
        mtrace("Sending out mod_challenge match invitations for " . count($records) . " matches.");
        $games_by_rounds = [];
        $courses_by_rounds = [];
        foreach ($records as $record) {
            // load match
            $match = new match();
            $match->apply($record);

            // load game
            if (!isset($games_by_rounds[$match->get_round()])) {
                $round = util::get_round($match->get_round());
                $game = util::get_game_by_id($round->get_game());
                $games_by_rounds[$match->get_round()] = $game;
                $course = $DB->get_record('course', ['id' => $game->get_course()]);
                $courses_by_rounds[$match->get_round()] = $course;
            }
            $game = $games_by_rounds[$match->get_round()];
            $course = $courses_by_rounds[$match->get_round()];

            // send invitation for opponent 1
            if (!$match->is_mdl_user_1_notified()) {
                try {
                    $this->send_invitation(intval($match->get_mdl_user_1()), intval($match->get_mdl_user_2()), $match, $game, $course);
                    $match->set_mdl_user_1_notified(true);
                    $match->save();
                } catch (moodle_exception $ignored) {
                }
            }

            // send invitation for opponent 2
            if (!$match->is_mdl_user_2_notified()) {
                try {
                    $this->send_invitation(intval($match->get_mdl_user_2()), intval($match->get_mdl_user_1()), $match, $game, $course);
                    $match->set_mdl_user_2_notified(true);
                    $match->save();
                } catch (moodle_exception $ignored) {
                }
            }
        }
    }

    /**
     * Sends out an invitation for the match to the specified user.
     *
     * @param int $mdl_user_to
     * @param int $mdl_user_from
     * @param match $match
     * @param game $game
     * @param stdClass $course
     *
     * @throws coding_exception|moodle_exception
     */
    private function send_invitation(int $mdl_user_to, int $mdl_user_from, match $match, game $game, stdClass $course) {
        // set up context
        list($course, $cm) = get_course_and_cm_from_instance($game->get_id(), 'challenge');
        $user_to = new stdClass();
        $user_to->id = $mdl_user_to;
        cron_setup_user($user_to, $course);
        $user_from = core_user::get_noreply_user();
        $opponent = new stdClass();
        $opponent->id = $mdl_user_from;

        // build message renderers
        global $PAGE;
        $htmlout = $PAGE->get_renderer('mod_challenge', 'email', 'htmlemail');
        $textout = $PAGE->get_renderer('mod_challenge', 'email', 'textemail');

        // build message content
        $subject = get_string('message_match_invitation_subject', 'mod_challenge');
        $data = new challenge_match_invitation($course, $cm, $game, $match, $opponent, $user_to);

        // send message
        $eventdata = new message();
        $eventdata->courseid = $course->id;
        $eventdata->component = 'mod_challenge';
        $eventdata->name = 'match';
        $eventdata->userfrom = $user_from;
        $eventdata->userto = $user_to;
        $eventdata->subject = $subject;
        $eventdata->fullmessage = $textout->render($data);
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = $htmlout->render($data);
        $eventdata->notification = 1;
        message_send($eventdata);

        // clean up
        unset($user_to);
    }
}
