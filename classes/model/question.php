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

use function array_map;
use function explode;
use function intval;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Class tournament_question
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
    protected $mdl_user_winner;
    /**
     * @var int The id of the moodle user who got this question.
     */
    protected $mdl_user_1;
    /**
     * @var int The timestamp of when the user started the question.
     */
    protected $mdl_user_1_timestart;
    /**
     * @var int The id of the moodle answer the user has chosen.
     */
    protected $mdl_user_1_answer;
    /**
     * @var int The score the user has reached by answering this question.
     */
    protected $mdl_user_1_score;
    /**
     * @var bool Whether or not the question was answered correctly.
     */
    protected $mdl_user_1_correct;
    /**
     * @var bool Whether or not the question was answered at all.
     */
    protected $mdl_user_1_finished;
    /**
     * @var int The id of the moodle user who got this question.
     */
    protected $mdl_user_2;
    /**
     * @var int The timestamp of when the user started the question.
     */
    protected $mdl_user_2_timestart;
    /**
     * @var int The id of the moodle answer the user has chosen.
     */
    protected $mdl_user_2_answer;
    /**
     * @var int The score the user has reached by answering this question.
     */
    protected $mdl_user_2_score;
    /**
     * @var bool Whether or not the question was answered correctly.
     */
    protected $mdl_user_2_correct;
    /**
     * @var bool Whether or not the question was answered at all.
     */
    protected $mdl_user_2_finished;
    /**
     * @var \question_definition
     */
    protected $_mdl_question;

    /**
     * tournament_question constructor.
     */
    function __construct() {
        parent::__construct('challenge_questions', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->matchid = 0;
        $this->number = 0;
        $this->mdl_question = 0;
        $this->mdl_answers_order = '';
        $this->mdl_user_winner = 0;
        $this->mdl_user_1 = 0;
        $this->mdl_user_1_timestart = 0;
        $this->mdl_user_1_answer = 0;
        $this->mdl_user_1_score = 0;
        $this->mdl_user_1_correct = false;
        $this->mdl_user_1_finished = false;
        $this->mdl_user_2 = 0;
        $this->mdl_user_2_timestart = 0;
        $this->mdl_user_2_answer = 0;
        $this->mdl_user_2_score = 0;
        $this->mdl_user_2_correct = false;
        $this->mdl_user_2_finished = false;
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
        $this->mdl_user_winner = isset($data['mdl_user_winner']) ? $data['mdl_user_winner'] : 0;
        // user 1
        $this->mdl_user_1 = isset($data['mdl_user_1']) ? $data['mdl_user_1'] : 0;
        $this->mdl_user_1_timestart = isset($data['mdl_user_1_timestart']) ? $data['mdl_user_1_timestart'] : 0;
        $this->mdl_user_1_answer = isset($data['mdl_user_1_answer']) ? $data['mdl_user_1_answer'] : null;
        $this->mdl_user_1_score = isset($data['mdl_user_1_score']) ? $data['mdl_user_1_score'] : 0;
        $this->mdl_user_1_correct = isset($data['mdl_user_1_correct']) ? ($data['mdl_user_1_correct'] == 1) : false;
        $this->mdl_user_1_finished = isset($data['mdl_user_1_finished']) ? ($data['mdl_user_1_finished'] == 1) : false;
        // user 2
        $this->mdl_user_2 = isset($data['mdl_user_2']) ? $data['mdl_user_2'] : 0;
        $this->mdl_user_2_timestart = isset($data['mdl_user_2_timestart']) ? $data['mdl_user_2_timestart'] : 0;
        $this->mdl_user_2_answer = isset($data['mdl_user_2_answer']) ? $data['mdl_user_2_answer'] : null;
        $this->mdl_user_2_score = isset($data['mdl_user_2_score']) ? $data['mdl_user_2_score'] : 0;
        $this->mdl_user_2_correct = isset($data['mdl_user_2_correct']) ? ($data['mdl_user_2_correct'] == 1) : false;
        $this->mdl_user_2_finished = isset($data['mdl_user_2_finished']) ? ($data['mdl_user_2_finished'] == 1) : false;
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
     * Checks if the given moodle user has finished this question.
     *
     * @param int $mdl_user_id
     * @return bool
     */
    public function is_finished_by($mdl_user_id): bool {
        if ($this->is_mdl_user_1($mdl_user_id)) {
            return $this->mdl_user_1_finished;
        } elseif ($this->is_mdl_user_2($mdl_user_id)) {
            return $this->mdl_user_2_finished;
        } else {
            return false;
        }
    }

    /**
     * Gets the question start time of the given user.
     *
     * @param int $mdl_user_id
     * @return int
     */
    public function get_timestart($mdl_user_id): int {
        if ($this->is_mdl_user_1($mdl_user_id)) {
            return $this->get_mdl_user_1_timestart();
        } elseif($this->is_mdl_user_2($mdl_user_id)) {
            return $this->get_mdl_user_2_timestart();
        } else {
            return 0;
        }
    }

    /**
     * Checks if the given moodle user is the first match user.
     *
     * @param int $mdl_user_id
     * @return bool
     */
    public function is_mdl_user_1($mdl_user_id): bool {
        return $this->get_mdl_user_1() === $mdl_user_id;
    }

    /**
     * Checks if the given moodle user is the second match user.
     *
     * @param int $mdl_user_id
     * @return bool
     */
    public function is_mdl_user_2($mdl_user_id): bool {
        return $this->get_mdl_user_2() === $mdl_user_id;
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
    public function set_mdl_question(int $mdl_question): void {
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
    public function set_mdl_answers_order(string $mdl_answers_order): void {
        $this->mdl_answers_order = $mdl_answers_order;
    }

    /**
     * @return int
     */
    public function get_mdl_user_winner(): int {
        return $this->mdl_user_winner;
    }

    /**
     * @param int $mdl_user_winner
     */
    public function set_mdl_user_winner(int $mdl_user_winner): void {
        $this->mdl_user_winner = $mdl_user_winner;
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
    public function set_mdl_user_1(int $mdl_user_1): void {
        $this->mdl_user_1 = $mdl_user_1;
    }

    /**
     * @return int
     */
    public function get_mdl_user_1_timestart(): int {
        return $this->mdl_user_1_timestart;
    }

    /**
     * @param int $mdl_user_1_timestart
     */
    public function set_mdl_user_1_timestart(int $mdl_user_1_timestart): void {
        $this->mdl_user_1_timestart = $mdl_user_1_timestart;
    }

    /**
     * @return int
     */
    public function get_mdl_user_1_answer(): int {
        return $this->mdl_user_1_answer;
    }

    /**
     * @param int $mdl_user_1_answer
     */
    public function set_mdl_user_1_answer(int $mdl_user_1_answer): void {
        $this->mdl_user_1_answer = $mdl_user_1_answer;
    }

    /**
     * @return int
     */
    public function get_mdl_user_1_score(): int {
        return $this->mdl_user_1_score;
    }

    /**
     * @param int $mdl_user_1_score
     */
    public function set_mdl_user_1_score(int $mdl_user_1_score): void {
        $this->mdl_user_1_score = $mdl_user_1_score;
    }

    /**
     * @return bool
     */
    public function is_mdl_user_1_correct(): bool {
        return $this->mdl_user_1_correct;
    }

    /**
     * @param bool $mdl_user_1_correct
     */
    public function set_mdl_user_1_correct(bool $mdl_user_1_correct): void {
        $this->mdl_user_1_correct = $mdl_user_1_correct;
    }

    /**
     * @return bool
     */
    public function is_mdl_user_1_finished(): bool {
        return $this->mdl_user_1_finished;
    }

    /**
     * @param bool $mdl_user_1_finished
     */
    public function set_mdl_user_1_finished(bool $mdl_user_1_finished): void {
        $this->mdl_user_1_finished = $mdl_user_1_finished;
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
    public function set_mdl_user_2(int $mdl_user_2): void {
        $this->mdl_user_2 = $mdl_user_2;
    }

    /**
     * @return int
     */
    public function get_mdl_user_2_timestart(): int {
        return $this->mdl_user_2_timestart;
    }

    /**
     * @param int $mdl_user_2_timestart
     */
    public function set_mdl_user_2_timestart(int $mdl_user_2_timestart): void {
        $this->mdl_user_2_timestart = $mdl_user_2_timestart;
    }

    /**
     * @return int
     */
    public function get_mdl_user_2_answer(): int {
        return $this->mdl_user_2_answer;
    }

    /**
     * @param int $mdl_user_2_answer
     */
    public function set_mdl_user_2_answer(int $mdl_user_2_answer): void {
        $this->mdl_user_2_answer = $mdl_user_2_answer;
    }

    /**
     * @return int
     */
    public function get_mdl_user_2_score(): int {
        return $this->mdl_user_2_score;
    }

    /**
     * @param int $mdl_user_2_score
     */
    public function set_mdl_user_2_score(int $mdl_user_2_score): void {
        $this->mdl_user_2_score = $mdl_user_2_score;
    }

    /**
     * @return bool
     */
    public function is_mdl_user_2_correct(): bool {
        return $this->mdl_user_2_correct;
    }

    /**
     * @param bool $mdl_user_2_correct
     */
    public function set_mdl_user_2_correct(bool $mdl_user_2_correct): void {
        $this->mdl_user_2_correct = $mdl_user_2_correct;
    }

    /**
     * @return bool
     */
    public function is_mdl_user_2_finished(): bool {
        return $this->mdl_user_2_finished;
    }

    /**
     * @param bool $mdl_user_2_finished
     */
    public function set_mdl_user_2_finished(bool $mdl_user_2_finished): void {
        $this->mdl_user_2_finished = $mdl_user_2_finished;
    }
}
