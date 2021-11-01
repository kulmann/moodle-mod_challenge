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
use core\task\scheduled_task;
use core_user;
use dml_exception;
use mod_challenge\model\game;
use mod_challenge\model\match;
use mod_challenge\model\message;
use mod_challenge\model\round;
use mod_challenge\util;
use moodle_exception;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class send_messages
 *
 * @package    mod_challenge\model
 * @copyright  2021 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_messages extends scheduled_task {

    /**
     * @var game[] Cached games
     */
    private $cache_game = [];
    /**
     * @var round[] Cached rounds
     */
    private $cache_round = [];

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('task_send_messages', 'mod_challenge');
    }

    /**
     * Execute the task.
     *
     * @throws dml_exception
     */
    public function execute() {
        // generating messages
        mtrace("... generating quiz challenge messages");
        $generate_count = $this->generate_messages();
        mtrace("... generated {$generate_count} quiz challenge messages");

        // sending messages
        mtrace("... sending out quiz challenge messages");
        $sent_count = $this->send_messages();
        mtrace("... sent out {$sent_count} quiz challenge messages");




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

    private function generate_messages(): int {
        $count = 0;
        foreach (message::VALID_TYPES as $type) {
            $function_name = "generate_messages_{$type}";
            $count += $this->$function_name();
        }
        return $count;
    }

    private function generate_messages_match_started(): int {

    }

    private function generate_messages_match_stale(): int {

    }

    private function generate_messages_match_finished(): int {

    }

    private function generate_messages_opponent_player(): int {

    }

    private function send_messages(): int {
        global $DB;
        $records = $DB->get_records_sql("SELECT * FROM {challenge_messages}", null, 0, 200);
        foreach ($records as $record) {
            // load message
            $message = new message();
            $message->apply($record);

            // send message
            $this->send_message($message);
        }
        return \count($records);
    }

    private function send_message(message $message) {
        // set up context
        global $DB;
        $game = $this->get_cached_game($message->get_game());
        $round = $this->get_cached_round($message->get_round());
        $match = util::get_match($message->get_matchid());
        $mdl_user_to = $DB->get_record('user', array('id' => $message->get_mdl_user()));
        list($course, $cm) = get_course_and_cm_from_instance($game->get_id(), 'challenge');
        cron_setup_user($mdl_user_to);

        // build message content
        $data = new stdClass();
        $data->fullname = $mdl_user_to->firstname . " " . $mdl_user_to->lastname;
        $data->coursename = $course->fullname;
        $data->roundnumber = $round->get_number();
        $data->gamename = $game->get_name();
        $data->matchurl = $this->get_match_url($cm->id, $match->get_id());

        // send message
        $eventdata = new core\message\message();
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
    private function get_match_url(int $cmid, int $matchid) {
        $link = new moodle_url(
            "/mod/challenge/view.php/$cmid/game/matches/$matchid"
        );
        return $link->out(false);
    }

    /**
     * Gets the game with the given id from an in memory cache. Loads it if necessary.
     *
     * @param int $gameid
     * @return game Game with the given id
     * @throws dml_exception
     */
    private function get_cached_game(int $gameid): game {
        if (!isset($this->cache_game[$gameid])) {
            $this->cache_game[$gameid] = util::get_game_by_id($gameid);
        }
        return $this->cache_game[$gameid];
    }

    /**
     * Gets the round with the given id from an in memory cache. Loads it if necessary.
     *
     * @param int $roundid
     * @return round Round with the given id
     * @throws dml_exception
     */
    private function get_cached_round(int $roundid): round {
        if (!isset($this->cache_round[$roundid])) {
            $this->cache_round[$roundid] = util::get_round($roundid);
        }
        return $this->cache_round[$roundid];
    }
}
