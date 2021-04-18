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

use dml_exception;
use function array_map;
use function explode;
use function intval;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Class question
 *
 * @package    mod_challenge\model
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question extends abstract_model {

    /**
     * @var int The timestamp of the creation of this question.
     */
    protected $timecreated;
    /**
     * @var int The timestamp of the last update of this question.
     */
    protected $timemodified;
    /**
     * @var int The id of the associated match.
     */
    protected $matchid;
    /**
     * @var int The position within the question set of the match.
     */
    protected $number;
    /**
     * @var int The id of the moodle question.
     */
    protected $mdl_question;
    /**
     * @var string The ids of the moodle answers in their correct order, separated by commas.
     */
    protected $mdl_answers_order;
    /**
     * @var int The id of the moodle user who won this question.
     */
    protected $winner_mdl_user;
    /**
     * @var int The score of the winning user.
     */
    protected $winner_score;
    /**
     * @var \question_definition
     */
    protected $_mdl_question;

    /**
     * question constructor.
     */
    function __construct() {
        parent::__construct('challenge_questions', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->matchid = 0;
        $this->number = 0;
        $this->mdl_question = 0;
        $this->mdl_answers_order = '';
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
        $this->matchid = $data['matchid'];
        $this->number = $data['number'];
        $this->mdl_question = $data['mdl_question'];
        $this->mdl_answers_order = $data['mdl_answers_order'];
        $this->winner_mdl_user = isset($data['winner_mdl_user']) ? $data['winner_mdl_user'] : 0;
        $this->winner_score = isset($data['winner_score']) ? $data['winner_score'] : 0;
    }

    /**
     * Returns the moodle question from the question bank.
     *
     * @return \question_definition
     */
    public function get_mdl_question_ref(): \question_definition {
        if ($this->_mdl_question === null) {
            $this->_mdl_question = \question_bank::load_question($this->mdl_question, false);
        }
        return $this->_mdl_question;
    }

    /**
     * Returns the ids of the moodle answer ids in their stored order.
     *
     * @return int[]
     */
    public function get_mdl_answer_ids_ordered(): array {
        return array_map(
            function ($strId) {
                return intval($strId);
            },
            explode(",", $this->get_mdl_answers_order())
        );
    }

    /**
     * Loads all attempts of this question ([0-2] attempts).
     *
     * @return attempt[]
     * @throws dml_exception
     */
    public function get_attempts(): array {
        global $DB;
        $records = $DB->get_records('challenge_attempts', ['question' => $this->get_id()]);
        return array_map(function ($record) {
            $attempt = new attempt();
            $attempt->load_data_by_id($record->id);
            return $attempt;
        }, $records);
    }

    /**
     * Try to find an attempt for this question by the given $userid.
     *
     * @param int $userid
     * @return attempt|null
     * @throws dml_exception
     */
    public function get_attempt_by_user(int $userid) {
        global $DB;
        $record = $DB->get_record('challenge_attempts', ['question' => $this->get_id(), 'mdl_user' => $userid]);
        if ($record !== false) {
            $attempt = new attempt();
            $attempt->apply($record);
            return $attempt;
        }
        return null;
    }

    /**
     * Tries to find an attempt for this question by the given $userid, creates one if none found. And then closes it.
     *
     * @param int $userid
     * @throws dml_exception
     */
    public function close_attempt(int $userid) {
        $attempt = $this->get_attempt_by_user($userid);
        if ($attempt === null) {
            $attempt = new attempt();
            $attempt->set_question($this->get_id());
            $attempt->set_mdl_user($userid);
        } elseif($attempt->is_finished()) {
            return;
        }
        $attempt->set_finished(true);
        $attempt->set_timeremaining(0);
        $attempt->save();
    }

    /**
     * Checks if the attempts for this question are finished and determines a winner if not done already.
     *
     * @param game $game
     * @throws dml_exception
     */
    public function check_winner(game $game) {
        if ($this->is_finished()) {
            return;
        }
        $attempts = $this->get_attempts();
        $count_finished = 0;
        foreach($attempts as $attempt) {
            $attempt->check_time_exceeded($game);
            if ($attempt->is_finished()) {
                $count_finished++;
            }
        }
        if ($count_finished === 2) {
            usort($attempts, function(attempt $a1, attempt $a2) {
                if ($a1->is_correct() === $a2->is_correct()) {
                    if ($a1->get_score() === $a2->get_score()) {
                        return $a1->get_timecreated() <=> $a2->get_timecreated();
                    }
                    return $a2->get_score() <=> $a1->get_score();
                }
                return $a2->is_correct() <=> $a1->is_correct();
            });
            $winner = reset($attempts);
            assert($winner instanceof attempt);
            $this->set_winner_mdl_user($winner->get_mdl_user());
            $this->set_winner_score($winner->get_score());
            $this->save();
        }
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
    public function get_matchid(): int {
        return $this->matchid;
    }

    /**
     * @param int $matchid
     */
    public function set_matchid(int $matchid) {
        $this->matchid = $matchid;
    }

    /**
     * @return int
     */
    public function get_number(): int {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function set_number(int $number) {
        $this->number = $number;
    }

    /**
     * @return int
     */
    public function get_mdl_question(): int {
        return $this->mdl_question;
    }

    /**
     * @param int $mdl_question
     */
    public function set_mdl_question(int $mdl_question) {
        $this->mdl_question = $mdl_question;
    }

    /**
     * @return string
     */
    public function get_mdl_answers_order(): string {
        return $this->mdl_answers_order;
    }

    /**
     * @param string $mdl_answers_order
     */
    public function set_mdl_answers_order(string $mdl_answers_order) {
        $this->mdl_answers_order = $mdl_answers_order;
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
     * Returns whether this question is already finished.
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
