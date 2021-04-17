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
use mod_challenge\model\round;
use mod_challenge\util;
use moodle_exception;
use moodle_url;
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
        mtrace("... sending out match invitations for " . count($records) . " matches.");
        $games_by_rounds = [];
        $rounds_by_id = [];
        foreach ($records as $record) {
            // load match
            $match = new match();
            $match->apply($record);

            // load game
            if (!isset($rounds_by_id[$match->get_round()])) {
                $round = util::get_round($match->get_round());
                $rounds_by_id[$match->get_round()] = $round;
            }
            $round = $rounds_by_id[$match->get_round()];
            if (!isset($games_by_rounds[$match->get_round()])) {
                $game = util::get_game_by_id($round->get_game());
                $games_by_rounds[$match->get_round()] = $game;
            }
            $game = $games_by_rounds[$match->get_round()];

            // send invitation for opponent 1
            if (!$match->is_mdl_user_1_notified()) {
                try {
                    $this->send_invitation($match->get_mdl_user_1(), $game, $round, $match);
                    $match->set_mdl_user_1_notified(true);
                    $match->save();
                } catch (moodle_exception $ignored) {
                }
            }

            // send invitation for opponent 2
            if (!$match->is_mdl_user_2_notified()) {
                try {
                    $this->send_invitation($match->get_mdl_user_2(), $game, $round, $match);
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
     * @param game $game
     * @param round $round
     * @param match $match
     *
     * @throws coding_exception|moodle_exception
     */
    private function send_invitation(int $mdl_user_to, game $game, round $round, match $match) {
        // set up context
        global $DB;
        $user_to = $DB->get_record('user', array('id' => $mdl_user_to));
        list($course, $cm) = get_course_and_cm_from_instance($game->get_id(), 'challenge');
        cron_setup_user($user_to);

        // build message content
        $data = new stdClass();
        $data->fullname = $user_to->firstname . " " . $user_to->lastname;
        $data->coursename = $course->fullname;
        $data->roundnumber = $round->get_number();
        $data->gamename = $game->get_name();
        $data->matchurl = $this->get_match_url($cm->id, $match->get_id());

        // send message
        $eventdata = new message();
        $eventdata->courseid = $course->id;
        $eventdata->component = 'mod_challenge';
        $eventdata->name = 'match';
        $eventdata->userfrom = core_user::get_noreply_user();
        $eventdata->userto = $mdl_user_to;
        $eventdata->subject = get_string('message_match_invitation_subject', 'challenge');
        $eventdata->fullmessage = get_string('message_match_invitation_message_plain', 'challenge', $data);
        $eventdata->fullmessagehtml = get_string('message_match_invitation_message_html', 'challenge', $data);
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->notification = 1;//this is only set to 0 for personal messages between users
        message_send($eventdata);

        // clean up
        unset($user_to);
    }

    /**
     * Get the link to the match.
     *
     * @param int $cmid
     * @param int $matchid
     *
     * @return string
     */
    public function get_match_url(int $cmid, int $matchid) {
        $link = new moodle_url(
            "/mod/challenge/view.php/$cmid/game/matches/$matchid"
        );
        return $link->out(false);
    }
}
