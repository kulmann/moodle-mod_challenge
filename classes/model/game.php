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
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class game extends abstract_model {

    const STATE_FINISHED = 'finished';
    const STATE_DUMPED = 'dumped';
    const STATE_PROGRESS = 'progress';
    const STATE_UNPUBLISHED = 'unpublished';
    const STATES = [
        self::STATE_FINISHED,
        self::STATE_DUMPED,
        self::STATE_PROGRESS,
        self::STATE_UNPUBLISHED,
    ];

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
     * @var int The time unit for duration of a round (see lib.php).
     */
    protected $round_duration_unit;
    /**
     * @var int The value for duration of a round.
     */
    protected $round_duration_value;
    /**
     * @var int The number of rounds until the game ends.
     */
    protected $rounds;
    /**
     * @var int The moodle user id of the winner
     */
    protected $winner_mdl_user;
    /**
     * @var int The final score of the winning user
     */
    protected $winner_score;
    /**
     * @var string The state of this game.
     */
    protected $state;

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
        $this->round_duration_unit = MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS;
        $this->round_duration_value = 7;
        $this->rounds = 10;
        $this->winner_mdl_user = 0;
        $this->winner_score = 0;
        $this->state = self::STATE_UNPUBLISHED;
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
        $this->round_duration_unit = isset($data['round_duration_unit']) ? $data['round_duration_unit'] : MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS;
        $this->round_duration_value = isset($data['round_duration_value']) ? $data['round_duration_value'] : 7;
        $this->rounds = isset($data['rounds']) ? $data['rounds'] : 10;
        $this->winner_mdl_user = isset($data['winner_mdl_user']) ? $data['winner_mdl_user'] : 0;
        $this->winner_score = isset($data['winner_score']) ? $data['winner_score'] : 0;
        $this->state = isset($data['state']) ? $data['state'] : self::STATE_UNPUBLISHED;
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
     * @return int
     */
    public function get_course(): int {
        return $this->course;
    }

    /**
     * @return string
     */
    public function get_name(): string {
        return $this->name;
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
    public function get_round_duration_unit(): int {
        return $this->round_duration_unit;
    }

    /**
     * @return int
     */
    public function get_round_duration_value(): int {
        return $this->round_duration_value;
    }

    /**
     * Calculates the duration of one round as seconds.
     *
     * @return int
     */
    public function calculate_round_duration_seconds(): int {
        $factor = $this->determine_round_duration_factor();
        return $this->round_duration_value * $factor;
    }

    /**
     * Determines the factor for translating the round duration to seconds.
     *
     * @return int
     */
    private function determine_round_duration_factor(): int {
        switch($this->round_duration_unit) {
            case MOD_CHALLENGE_ROUND_DURATION_UNIT_HOURS:
                return 60 * 60;
            case MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS:
                return 60 * 60 * 24;
            case MOD_CHALLENGE_ROUND_DURATION_UNIT_WEEKS:
            default:
                return 60 * 60 * 24 * 7;
        }
    }

    /**
     * @return int
     */
    public function get_rounds(): int {
        return $this->rounds;
    }

    /**
     * @return int
     */
    public function get_winner_mdl_user(): int {
        return $this->winner_mdl_user;
    }

    /**
     * @return int
     */
    public function get_winner_score(): int {
        return $this->winner_score;
    }

    /**
     * @return string
     */
    public function get_state(): string {
        return $this->state;
    }
}
