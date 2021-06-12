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
 * Class attempt
 *
 * @package    mod_challenge\model
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt extends abstract_model {

    /**
     * @var int The timestamp of the creation of this question.
     */
    protected $timecreated;
    /**
     * @var int The timestamp of the last update of this question.
     */
    protected $timemodified;
    /**
     * @var int The id of the associated question.
     */
    protected $question;
    /**
     * @var int The id of the moodle user who got this question.
     */
    protected $mdl_user;
    /**
     * @var int The id of the moodle answer the user has chosen.
     */
    protected $mdl_answer;
    /**
     * @var int The score the user has reached by answering this question.
     */
    protected $score;
    /**
     * @var bool Whether or not the question was answered correctly.
     */
    protected $correct;
    /**
     * @var bool Whether or not the question was answered at all.
     */
    protected $finished;
    /**
     * @var int The number of seconds remaining for this question.
     */
    protected $timeremaining;

    /**
     * tournament_question constructor.
     */
    function __construct() {
        parent::__construct('challenge_attempts', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->question = 0;
        $this->mdl_user = 0;
        $this->mdl_answer = 0;
        $this->score = 0;
        $this->correct = false;
        $this->finished = false;
        $this->timeremaining = -1;
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
        $this->question = $data['question'];
        $this->mdl_user = isset($data['mdl_user']) ? $data['mdl_user'] : 0;
        $this->mdl_answer = isset($data['mdl_answer']) ? $data['mdl_answer'] : null;
        $this->score = isset($data['score']) ? $data['score'] : 0;
        $this->correct = isset($data['correct']) ? ($data['correct'] == 1) : false;
        $this->finished = isset($data['finished']) ? ($data['finished'] == 1) : false;
        $this->timeremaining = isset($data['timeremaining']) ? $data['timeremaining'] : -1;
    }

    /**
     * Checks if this attempt should be ended.
     *
     * @param game $game
     * @throws dml_exception
     */
    public function check_time_exceeded(game $game) {
        if (!$this->is_finished() && $this->get_timecreated() + $game->get_question_duration() < time()) {
            $this->set_mdl_answer(0);
            $this->set_finished(true);
            $this->set_correct(false);
            $this->set_score(0);
            $this->set_timeremaining(0);
            $this->save();
        }
    }

    /**
     * Asserts whether this attempt is an actual question answer or just for filling up the question on match end.
     *
     * @return bool
     */
    public function is_answered() {
        return $this->mdl_answer > 0;
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
    public function get_question(): int {
        return $this->question;
    }

    /**
     * @param int $question
     */
    public function set_question(int $question) {
        $this->question = $question;
    }

    /**
     * @return int
     */
    public function get_mdl_user(): int {
        return $this->mdl_user;
    }

    /**
     * @param int $mdl_user
     */
    public function set_mdl_user(int $mdl_user) {
        $this->mdl_user = $mdl_user;
    }

    /**
     * @return int
     */
    public function get_mdl_answer(): int {
        return $this->mdl_answer;
    }

    /**
     * @param int $mdl_answer
     */
    public function set_mdl_answer(int $mdl_answer) {
        $this->mdl_answer = $mdl_answer;
    }

    /**
     * @return int
     */
    public function get_score(): int {
        return $this->score;
    }

    /**
     * @param int $score
     */
    public function set_score(int $score) {
        $this->score = $score;
    }

    /**
     * @return bool
     */
    public function is_correct(): bool {
        return $this->correct;
    }

    /**
     * @param bool $correct
     */
    public function set_correct(bool $correct) {
        $this->correct = $correct;
    }

    /**
     * @return bool
     */
    public function is_finished(): bool {
        return $this->finished;
    }

    /**
     * @param bool $finished
     */
    public function set_finished(bool $finished) {
        $this->finished = $finished;
    }

    /**
     * @return int
     */
    public function get_timeremaining(): int {
        return $this->timeremaining;
    }

    /**
     * @param int $timeremaining
     */
    public function set_timeremaining(int $timeremaining) {
        $this->timeremaining = $timeremaining;
    }
}
