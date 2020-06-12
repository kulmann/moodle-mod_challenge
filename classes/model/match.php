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

namespace mod_challenge\model;

use mod_challenge\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tournament_match
 *
 * @package    mod_challenge\model
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class match extends abstract_model {

    /**
     * @var int The timestamp of the creation of this match.
     */
    protected $timecreated;
    /**
     * @var int The timestamp of the last update of this match.
     */
    protected $timemodified;
    /**
     * @var int The id of the round instance this match belongs to.
     */
    protected $round;
    /**
     * @var int The id of the first user.
     */
    protected $mdl_user_1;
    /**
     * @var bool Whether or not the user has been notified.
     */
    protected $mdl_user_1_notified;
    /**
     * @var int The id of the second user.
     */
    protected $mdl_user_2;
    /**
     * @var bool Whether or not the user has been notified.
     */
    protected $mdl_user_2_notified;
    /**
     * @var int The user id of the winner of this match.
     */
    protected $winner_mdl_user;
    /**
     * @var int The score of the winning user.
     */
    protected $winner_score;

    /**
     * tournament_match constructor.
     */
    function __construct() {
        parent::__construct('challenge_matches', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->round = 0;
        $this->mdl_user_1 = 0;
        $this->mdl_user_1_notified = false;
        $this->mdl_user_2 = 0;
        $this->mdl_user_2_notified = false;
        $this->winner_mdl_user = 0;
        $this->winner_score = 0;
    }

    /**
     * Apply data to this object from an associative array or an object.
     *
     * @param mixed $data
     *
     * @return void
     */
    public function apply($data) {
        if (\is_object($data)) {
            $data = get_object_vars($data);
        }
        $this->id = isset($data['id']) ? $data['id'] : 0;
        $this->timecreated = isset($data['timecreated']) ? $data['timecreated'] : \time();
        $this->timemodified = isset($data['timemodified']) ? $data['timemodified'] : \time();
        $this->round = $data['round'];
        $this->mdl_user_1 = $data['mdl_user_1'];
        $this->mdl_user_1_notified = isset($data['mdl_user_1_notified']) ? intval($data['mdl_user_1_notified']) === 1 : false;
        $this->mdl_user_2 = $data['mdl_user_2'];
        $this->mdl_user_2_notified = isset($data['mdl_user_2_notified']) ? intval($data['mdl_user_2_notified']) === 1 : false;
        $this->winner_mdl_user = isset($data['winner_mdl_user']) ? $data['winner_mdl_user'] : 0;
        $this->winner_score = isset($data['winner_score']) ? $data['winner_score'] : 0;
    }

    /**
     * Check if all questions have been flagged as finished and determine this match's winner.
     *
     * This should be executed when
     * a) a match gets potentially closed (i.e. when a question gets answered), or
     * b) a round exceeds it's lifetime and gets stopped.
     * There are no other situations in which this should be called, as there are a lot of
     * database queries involved.
     *
     * @param game $game
     * @param round $round
     *
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public function check_winner(game $game, round $round) {
        // early returns for performance
        if ($this->is_finished()) {
            return;
        }
        if (!$round->is_started()) {
            return;
        }

        // make sure that the questions have the most recent winner-state already
        $questions = $this->get_questions();
        $finished = 0;
        $win_counts = [];
        $score_sum = [];
        foreach ($questions as $question) {
            $question->check_winner($game);
            if ($question->is_finished()) {
                $finished++;
                $win_counts[$question->get_winner_mdl_user()]++;
                $score_sum[$question->get_winner_mdl_user()] += $question->get_winner_score();
            }
        }

        // if match is over, add empty questions and lost attempts where necessary
        if ($round->is_ended() && $finished !== $round->get_questions()) {
            // add empty questions
            for ($index = $finished; $index < $round->get_questions(); $index++) {
                $questions[] = $this->create_question($game, $round, $index + 1);
            }
            // go through unfinished questions and add lost attempts
            foreach ($questions as $question) {
                if (!$question->is_finished()) {
                    $question->close_attempt($this->get_mdl_user_1());
                    $question->close_attempt($this->get_mdl_user_2());
                    $question->check_winner($game);
                    if ($question->is_finished()) {
                        $finished++;
                        $win_counts[$question->get_winner_mdl_user()]++;
                        $score_sum[$question->get_winner_mdl_user()] += $question->get_winner_score();
                    }
                }
            }
        }

        // check if the finished questions match the required number of questions
        if ($finished !== $round->get_questions()) {
            return;
        }

        // check if there's a winner by win count
        if (max($win_counts) !== min($win_counts)) {
            $winner_as_array = array_keys($win_counts, max($win_counts));
            $mdl_user_winner = reset($winner_as_array);
            $this->set_winner_mdl_user($mdl_user_winner);
            $this->set_winner_score($score_sum[$mdl_user_winner]);
        }

        // check if there's a winner by score sum
        if (max($score_sum) !== min($score_sum)) {
            $winner_as_array = array_keys($score_sum, max($score_sum));
            $mdl_user_winner = reset($winner_as_array);
            $this->set_winner_mdl_user($mdl_user_winner);
            $this->set_winner_score($score_sum[$mdl_user_winner]);
        }

        // not likely, but if there is no winner by win count or score, pick first one
        $winner_as_array = array_keys($win_counts);
        $mdl_user_winner = reset($winner_as_array);
        $this->set_winner_mdl_user($mdl_user_winner);
        $this->set_winner_score($score_sum[$mdl_user_winner]);

        // save the changes
        $this->save();
    }

    /**
     * Create a question for this match, using the given $game and $round.
     *
     * @param game $game
     * @param round $round
     * @param int $number
     * @return question
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function create_question(game $game, round $round, int $number) {
        // create question
        $question = new question();
        $question->set_matchid($this->get_id());
        $question->set_number($number);

        // set moodle question
        $active_categories = $game->get_categories_by_round($this->get_round());
        $mdl_question = $round->get_random_mdl_question($active_categories);
        $question->set_mdl_question($mdl_question->id);

        // fixate the answer order
        $mdl_answer_ids = array_map(
            function ($mdl_answer) {
                return $mdl_answer->id;
            },
            $mdl_question->answers
        );
        if ($game->is_question_shuffle_answers()) {
            shuffle($mdl_answer_ids);
        }
        $question->set_mdl_answers_order(implode(",", $mdl_answer_ids));

        // done. save it
        $question->save();
        return $question;
    }

    /**
     * Gets all questions associated with this match.
     *
     * @return question[]
     * @throws \dml_exception
     */
    public function get_questions() {
        global $DB;
        $sql = "
                SELECT question.*
                  FROM {challenge_questions} question 
                 WHERE question.matchid = :matchid
              ORDER BY question.number ASC
        ";
        $sql_conditions = ['matchid' => $this->get_id()];
        $records = $DB->get_records_sql($sql, $sql_conditions);
        $result = [];
        foreach ($records as $record) {
            $question = new question();
            $question->apply($record);
            $result[] = $question;
        }
        return $result;
    }

    /**
     * Gets the question for the given number of this match, or returns null if it doesn't exist, yet.
     *
     * @param int $number
     * @return question|null
     * @throws \dml_exception
     */
    public function get_question_by_number($number) {
        global $DB;
        $sql = "SELECT question.*
                  FROM {challenge_questions} question
                 WHERE question.matchid = :matchid AND question.number = :number
        ";
        $sql_conditions = ['matchid' => $this->get_id(), 'number' => $number];
        $record = $DB->get_record_sql($sql, $sql_conditions);
        if ($record !== false) {
            $question = new question();
            $question->apply($record);
            return $question;
        }
        return null;
    }

    /**
     * @return int
     */
    public function get_timecreated(): int {
        return $this->timecreated;
    }

    /**
     * @return int
     */
    public function get_timemodified(): int {
        return $this->timemodified;
    }

    /**
     * @param int $timemodified
     */
    public function set_timemodified(int $timemodified) {
        $this->timemodified = $timemodified;
    }

    /**
     * @return int
     */
    public function get_round(): int {
        return $this->round;
    }

    /**
     * @param int $round
     */
    public function set_round(int $round) {
        $this->round = $round;
    }

    /**
     * @return int
     */
    public function get_mdl_user_1(): int {
        return $this->mdl_user_1;
    }

    /**
     * @param int $mdl_user_1
     */
    public function set_mdl_user_1(int $mdl_user_1) {
        $this->mdl_user_1 = $mdl_user_1;
    }

    /**
     * @return bool
     */
    public function is_mdl_user_1_notified(): bool {
        return $this->mdl_user_1_notified;
    }

    /**
     * @param bool $mdl_user_1_notified
     */
    public function set_mdl_user_1_notified(bool $mdl_user_1_notified) {
        $this->mdl_user_1_notified = $mdl_user_1_notified;
    }

    /**
     * @return int
     */
    public function get_mdl_user_2(): int {
        return $this->mdl_user_2;
    }

    /**
     * @param int $mdl_user_2
     */
    public function set_mdl_user_2(int $mdl_user_2) {
        $this->mdl_user_2 = $mdl_user_2;
    }

    /**
     * @return bool
     */
    public function is_mdl_user_2_notified(): bool {
        return $this->mdl_user_2_notified;
    }

    /**
     * @param bool $mdl_user_2_notified
     */
    public function set_mdl_user_2_notified(bool $mdl_user_2_notified) {
        $this->mdl_user_2_notified = $mdl_user_2_notified;
    }

    /**
     * @return int
     */
    public function get_winner_mdl_user(): int {
        return $this->winner_mdl_user;
    }

    /**
     * @param int $winner_mdl_user
     */
    public function set_winner_mdl_user(int $winner_mdl_user) {
        $this->winner_mdl_user = $winner_mdl_user;
    }

    /**
     * Returns whether this match is already finished.
     *
     * @return bool
     */
    public function is_finished() {
        return $this->winner_mdl_user > 0;
    }

    /**
     * @return int
     */
    public function get_winner_score(): int {
        return $this->winner_score;
    }

    /**
     * @param int $winner_score
     */
    public function set_winner_score(int $winner_score) {
        $this->winner_score = $winner_score;
    }
}
