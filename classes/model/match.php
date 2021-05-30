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
     * @var bool Whether or not the match is completed (either ended or answered by both participants).
     */
    protected $completed;
    /**
     * @var int The id of the first user.
     */
    protected $mdl_user_1;
    /**
     * @var bool Whether or not the user has been notified.
     */
    protected $mdl_user_1_notified;
    /**
     * @var bool Whether or not the user has completed the match.
     */
    protected $mdl_user_1_completed;
    /**
     * @var int The id of the second user.
     */
    protected $mdl_user_2;
    /**
     * @var bool Whether or not the user has been notified.
     */
    protected $mdl_user_2_notified;
    /**
     * @var bool Whether or not the user has completed the match.
     */
    protected $mdl_user_2_completed;
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
        $this->completed = false;
        $this->mdl_user_1 = 0;
        $this->mdl_user_1_notified = false;
        $this->mdl_user_1_completed = false;
        $this->mdl_user_2 = 0;
        $this->mdl_user_2_notified = false;
        $this->mdl_user_2_completed = false;
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
        $this->timecreated = $data['timecreated'] ?? \time();
        $this->timemodified = $data['timemodified'] ?? \time();
        $this->round = $data['round'];
        $this->completed = isset($data['completed']) && intval($data['completed']) === 1;
        $this->mdl_user_1 = $data['mdl_user_1'];
        $this->mdl_user_1_notified = isset($data['mdl_user_1_notified']) && intval($data['mdl_user_1_notified']) === 1;
        $this->mdl_user_1_completed = isset($data['mdl_user_1_completed']) && intval($data['mdl_user_1_completed']) === 1;
        $this->mdl_user_2 = $data['mdl_user_2'];
        $this->mdl_user_2_notified = isset($data['mdl_user_2_notified']) && intval($data['mdl_user_2_notified']) === 1;
        $this->mdl_user_2_completed = isset($data['mdl_user_2_completed']) && intval($data['mdl_user_2_completed']) === 1;
        $this->winner_mdl_user = $data['winner_mdl_user'] ?? 0;
        $this->winner_score = $data['winner_score'] ?? 0;
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
        if ($this->is_completed()) {
            return;
        }
        if (!$round->is_started()) {
            return;
        }

        // make sure that the questions have the most recent winner-state already and count finished questions
        $questions = $this->get_questions();
        $finished = 0;
        foreach ($questions as $question) {
            $question->check_winner($game);
            if ($question->is_finished()) {
                $finished++;
            }
        }

        // if match is over, add empty questions and lost attempts where necessary
        if ($round->is_ended() && $finished !== $round->get_questions()) {
            // add empty questions
            for ($index = count($questions); $index < $round->get_questions(); $index++) {
                $questions[] = $this->create_question($game, $round, $index + 1);
            }
            // go through unfinished questions (including the new ones) and add lost attempts where players didn't participate
            foreach ($questions as $question) {
                if (!$question->is_finished()) {
                    $question->close_attempt($this->get_mdl_user_1());
                    $question->close_attempt($this->get_mdl_user_2());
                    $question->check_winner($game);
                    if ($question->is_finished()) {
                        $finished++;
                    }
                }
            }
        }

        // update 'completed' info on match for both users and the match itself
        if (count($questions) === $game->get_question_count()) {
            $answered_questions_mdl_user_1 = $this->get_count_answered_questions($this->get_mdl_user_1(), $questions);
            if ($answered_questions_mdl_user_1 === $game->get_question_count()) {
                $this->set_mdl_user_1_completed(true);
            }
            $answered_questions_mdl_user_2 = $this->get_count_answered_questions($this->get_mdl_user_2(), $questions);
            if ($answered_questions_mdl_user_2 === $game->get_question_count()) {
                $this->set_mdl_user_2_completed(true);
            }
            $completed_questions_mdl_user_1 = $this->get_count_completed_questions($this->get_mdl_user_1(), $questions);
            $completed_questions_mdl_user_2 = $this->get_count_completed_questions($this->get_mdl_user_2(), $questions);
            if ($completed_questions_mdl_user_1 === $game->get_question_count() && $completed_questions_mdl_user_2 === $game->get_question_count()) {
                $this->set_completed(true);
            }
            $this->save();
        }

        // check if the finished questions match the required number of questions
        if ($finished !== $round->get_questions()) {
            return;
        }

        // calculate score sum from finished questions
        $win_counts = [];
        $score_sum = [];
        foreach ($questions as $question) {
            if ($question->is_finished()) {
                $win_counts[$question->get_winner_mdl_user()]++;
                foreach ($question->get_attempts() as $attempt) {
                    $score_sum[$attempt->get_mdl_user()] += $attempt->get_score();
                }
            }
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
     * Counts the number of completed questions for the given user id.
     *
     * @param int $mdl_user
     * @param question[] $questions
     * @return int
     */
    private function get_count_completed_questions(int $mdl_user, array $questions) {
        $completed = 0;
        foreach ($questions as $question) {
            $attempt = $question->get_attempt_by_user($mdl_user);
            if ($attempt !== null && $attempt->is_finished()) {
                $completed++;
            }
        }
        return $completed;
    }

    /**
     * Counts the number of answered questions for the given user id.
     *
     * @param int $mdl_user
     * @param question[] $questions
     * @return int
     */
    private function get_count_answered_questions(int $mdl_user, array $questions) {
        $answered = 0;
        foreach ($questions as $question) {
            $attempt = $question->get_attempt_by_user($mdl_user);
            if ($attempt !== null && $attempt->is_answered()) {
                $answered++;
            }
        }
        return $answered;
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
        $mdl_question = $round->get_random_mdl_question($active_categories, [$this->get_mdl_user_1(), $this->get_mdl_user_2()]);
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
     * @return bool
     */
    public function is_completed(): bool {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function set_completed(bool $completed) {
        $this->completed = $completed;
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
     * @return bool
     */
    public function is_mdl_user_1_completed(): bool {
        return $this->mdl_user_1_completed;
    }

    /**
     * @param bool $mdl_user_1_completed
     */
    public function set_mdl_user_1_completed(bool $mdl_user_1_completed): void {
        $this->mdl_user_1_completed = $mdl_user_1_completed;
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
     * @return bool
     */
    public function is_mdl_user_2_completed(): bool {
        return $this->mdl_user_2_completed;
    }

    /**
     * @param bool $mdl_user_2_completed
     */
    public function set_mdl_user_2_completed(bool $mdl_user_2_completed): void {
        $this->mdl_user_2_completed = $mdl_user_2_completed;
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
     * Returns whether the first user has answered their questions for the match.
     *
     * @param int $question_count The number of questions that are needed for this match.
     *
     * @return bool
     */
    public function is_mdl_user_1_finished(int $question_count): bool {
        return $this->is_mdl_user_finished($this->mdl_user_1, $question_count);
    }

    /**
     * Returns whether the second user has answered their questions for the match.
     *
     * @param int $question_count The number of questions that are needed for this match.
     *
     * @return bool
     */
    public function is_mdl_user_2_finished(int $question_count): bool {
        return $this->is_mdl_user_finished($this->mdl_user_2, $question_count);
    }

    /**
     * Returns whether the given user has answered their questions for the match.
     *
     * @param int $user_id The id of the user to check the finished state for.
     * @param int $question_count The number of questions that are needed for this match.
     *
     * @return bool
     */
    private function is_mdl_user_finished(int $user_id, int $question_count): bool {
        $questions = $this->get_questions();
        if (count($questions) < $question_count) {
            return false;
        }
        foreach ($questions as $question) {
            $attempt = $question->get_attempt_by_user($user_id);
            if ($attempt === null || !$attempt->is_answered()) {
                return false;
            }
        }
        return true;
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
