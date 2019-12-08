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

use function assert;
use function usort;

defined('MOODLE_INTERNAL') || die();

/**
 * Class game
 *
 * @package    mod_challenge\model
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class game extends abstract_model {

    /**
     * @var int Timestamp of creation of this game.
     */
    protected $timecreated;
    /**
     * @var int Timestamp of last update of this game.
     */
    protected $timemodified;
    /**
     * @var int Id of course.
     */
    protected $course;
    /**
     * @var string Name of this game activity.
     */
    protected $name;
    /**
     * @var int The number of questions per tournament round.
     */
    protected $question_count;
    /**
     * @var int The number of seconds a question is shown for answering.
     */
    protected $question_duration;
    /**
     * @var int The number of seconds the solution is displayed before the ui goes to the next question.
     */
    protected $review_duration;
    /**
     * @var bool Whether or not answers should be shuffled when displaying a question.
     */
    protected $question_shuffle_answers;

    /**
     * game constructor.
     */
    function __construct() {
        parent::__construct('challenge', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->course = 0;
        $this->name = '';
        $this->question_count = 3;
        $this->question_duration = 30;
        $this->review_duration = 2;
        $this->question_shuffle_answers = true;
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
        $this->course = isset($data['course']) ? $data['course'] : 0;
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->question_count = isset($data['question_count']) ? $data['question_count'] : 3;
        $this->question_duration = isset($data['question_duration']) ? $data['question_duration'] : 30;
        $this->review_duration = isset($data['review_duration']) ? $data['review_duration'] : 2;
        $this->question_shuffle_answers = isset($data['question_shuffle_answers']) ? ($data['question_shuffle_answers'] == 1) : true;
    }

    /**
     * Loads all unpublished tournaments of this game.
     *
     * @return tournament[]
     * @throws \dml_exception
     */
    public function get_unpublished_tournaments() {
        return $this->get_tournaments_by_state(tournament::STATE_UNPUBLISHED);
    }

    /**
     * Loads all active tournaments of this game.
     *
     * @return tournament[]
     * @throws \dml_exception
     */
    public function get_active_tournaments() {
        return $this->get_tournaments_by_state(tournament::STATE_PROGRESS);
    }

    /**
     * Loads all finished tournaments of this game.
     *
     * @return tournament[]
     * @throws \dml_exception
     */
    public function get_finished_tournaments() {
        return $this->get_tournaments_by_state(tournament::STATE_FINISHED);
    }

    /**
     * Loads all tournaments of this game that match the given $state.
     *
     * @param string $state
     *
     * @return tournament[]
     * @throws \dml_exception
     */
    public function get_tournaments_by_state($state) {
        global $DB;
        $sql_params = ['game' => $this->get_id(), 'state' => $state];
        $records = $DB->get_records('challenge_tournaments', $sql_params, 'timecreated ASC');
        $result = [];
        foreach ($records as $tournament_data) {
            $tournament = new tournament();
            $tournament->apply($tournament_data);
            $result[] = $tournament;
        }
        return $result;
    }

    /**
     * Counts the active levels of this game.
     *
     * @return int
     * @throws \dml_exception
     */
    public function count_active_levels(): int {
        global $DB;
        $sql = "
            SELECT COUNT(id)
              FROM {challenge_levels}
             WHERE game = :game AND state = :state
        ";
        $count = $DB->get_field_sql($sql, ['game' => $this->get_id(), 'state' => level::STATE_ACTIVE]);
        return $count === false ? 0 : $count;
    }

    /**
     * Gets an active level which belongs to this game and has the given $position value. Will return null
     * if no such level exists.
     *
     * @param int $position
     *
     * @return level|null
     * @throws \dml_exception
     */
    public function get_active_level_by_position($position) {
        global $DB;
        $sql = "
            SELECT *
              FROM {challenge_levels}
             WHERE game = :game AND state = :state AND position = :position
        ";
        $record = $DB->get_record_sql($sql, ['game' => $this->get_id(), 'state' => level::STATE_ACTIVE, 'position' => $position]);
        if ($record === false) {
            return null;
        } else {
            $level = new level();
            $level->apply($record);
            return $level;
        }
    }

    /**
     * Gets all active levels for this game from the DB.
     *
     * @return level[]
     * @throws \dml_exception
     */
    public function get_active_levels() {
        global $DB;
        $sql_params = ['game' => $this->get_id(), 'state' => level::STATE_ACTIVE];
        $records = $DB->get_records('challenge_levels', $sql_params, 'position ASC');
        $result = [];
        foreach ($records as $level_data) {
            $level = new level();
            $level->apply($level_data);
            $result[] = $level;
        }
        return $result;
    }

    /**
     * Goes through all active levels, fixing their individual position.
     *
     * @return void
     * @throws \dml_exception
     */
    public function fix_level_positions() {
        $levels = $this->get_active_levels();
        // sort levels ascending
        usort($levels, function (level $level1, level $level2) {
            $pos1 = $level1->get_position();
            $pos2 = $level2->get_position();
            if ($pos1 === $pos2) {
                return 0;
            }
            return ($pos1 < $pos2) ? -1 : 1;
        });
        // walk through sorted list and set new positions
        $pos = 0;
        foreach ($levels as $level) {
            assert($level instanceof level);
            $level->set_position($pos++);
            $level->save();
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
    public function get_course(): int {
        return $this->course;
    }

    /**
     * @param int $course
     */
    public function set_course(int $course) {
        $this->course = $course;
    }

    /**
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function set_name(string $name) {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function get_question_count(): int {
        return $this->question_count;
    }

    /**
     * @return int
     */
    public function get_question_duration(): int {
        return $this->question_duration;
    }

    /**
     * @return int
     */
    public function get_review_duration(): int {
        return $this->review_duration;
    }

    /**
     * @return bool
     */
    public function is_question_shuffle_answers(): bool {
        return $this->question_shuffle_answers;
    }
}
