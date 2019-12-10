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

global $CFG;
require_once($CFG->libdir . '/outputcomponents.php');

use dml_exception;
use stdClass;
use user_picture;
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
     * @var int One of the tile height categories (see lib.php).
     */
    protected $level_tile_height;
    /**
     * @var int The alpha value of the level tile overlay (out of [0,100]).
     */
    protected $level_tile_alpha;

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
        $this->level_tile_height = MOD_CHALLENGE_LEVEL_TILE_HEIGHT_LARGE;
        $this->level_tile_alpha = 50;
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
        $this->level_tile_height = isset($data['level_tile_height']) ? $data['level_tile_height'] : MOD_CHALLENGE_LEVEL_TILE_HEIGHT_LARGE;
        $this->level_tile_alpha = isset($data['level_tile_alpha']) ? $data['level_tile_alpha'] : 50;
    }

    /**
     * Select all users within the given course.
     *
     * @param int $courseid
     *
     * @return stdClass[]
     * @throws dml_exception
     */
    public function get_mdl_users($courseid) {
        global $DB;
        $picture_fields = user_picture::fields('u');
        $sql = "SELECT DISTINCT $picture_fields
                FROM {user} u
                JOIN {role_assignments} a ON a.userid = u.id
                JOIN {context} ctx ON (a.contextid = ctx.id AND instanceid = :courseid)
                ORDER BY u.lastname, u.firstname ASC";
        $params = ['courseid' => $courseid];
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Loads all tournaments of this game.
     *
     * @return tournament[]
     * @throws dml_exception
     */
    public function get_tournaments() {
        global $DB;
        $sql_params = ['game' => $this->get_id(), 'state_dumped' => tournament::STATE_DUMPED];
        $records = $DB->get_records_sql("SELECT * 
                    FROM {challenge_tournaments}
                    WHERE game = :game  
                        AND state <> :state_dumped
                    ORDER BY timecreated DESC", $sql_params);
        $result = [];
        foreach ($records as $tournament_data) {
            $tournament = new tournament();
            $tournament->apply($tournament_data);
            $result[] = $tournament;
        }
        return $result;
    }

    /**
     * Loads all tournaments of this game where the given user is involved in a match.
     *
     * @param int $mdl_user_id
     *
     * @return tournament[]
     * @throws dml_exception
     */
    public function get_user_tournaments($mdl_user_id) {
        global $DB;
        $sql_params = [
            'game' => $this->get_id(),
            'user_1' => $mdl_user_id,
            'user_2' => $mdl_user_id,
            'state_unpublished' => tournament::STATE_UNPUBLISHED,
            'state_dumped' => tournament::STATE_DUMPED
        ];
        $records = $DB->get_records_sql("SELECT t.* 
                    FROM {challenge_tournaments} t
                    INNER JOIN {challenge_tnmt_matches} m ON t.id=m.tournament
                    WHERE t.game = :game 
                        AND (m.mdl_user_1 = :user_1 OR m.mdl_user_2 = :user_2) 
                        AND t.state <> :state_unpublished AND t.state <> :state_dumped
                    ORDER BY m.timecreated DESC", $sql_params);
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
     * @throws dml_exception
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
     * @throws dml_exception
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
     * @throws dml_exception
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
     * @throws dml_exception
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

    /**
     * @return int
     */
    public function get_level_tile_height(): int {
        return $this->level_tile_height;
    }

    /**
     * @return int
     */
    public function get_level_tile_height_px() {
        switch ($this->get_level_tile_height()) {
            case MOD_CHALLENGE_LEVEL_TILE_HEIGHT_SMALL:
                return 60;
            case MOD_CHALLENGE_LEVEL_TILE_HEIGHT_LARGE:
                return 200;
            case MOD_CHALLENGE_LEVEL_TILE_HEIGHT_MEDIUM:
            default:
                return 120;
        }
    }

    /**
     * @return int
     */
    public function get_level_tile_alpha() {
        return $this->level_tile_alpha;
    }
}
