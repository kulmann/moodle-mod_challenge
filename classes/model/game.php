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

use coding_exception;
use dml_exception;
use mod_challenge\util;
use moodle_exception;
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

    // round duration units
    const MOD_CHALLENGE_ROUND_DURATION_UNIT_HOURS = 'hours';
    const MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS = 'days';
    const MOD_CHALLENGE_ROUND_DURATION_UNIT_WEEKS = 'weeks';
    const MOD_CHALLENGE_ROUND_DURATION_UNITS = [
        self::MOD_CHALLENGE_ROUND_DURATION_UNIT_HOURS,
        self::MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS,
        self::MOD_CHALLENGE_ROUND_DURATION_UNIT_WEEKS,
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
     * @var int The number of matches being generated during a round.
     */
    protected $round_matches;
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
        $this->round_duration_unit = self::MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS;
        $this->round_duration_value = 7;
        $this->round_matches = 1;
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
        $this->id = $data['id'] ?? 0;
        $this->timecreated = $data['timecreated'] ?? \time();
        $this->timemodified = $data['timemodified'] ?? \time();
        $this->course = $data['course'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->question_count = $data['question_count'] ?? 3;
        $this->question_duration = $data['question_duration'] ?? 30;
        $this->review_duration = $data['review_duration'] ?? 2;
        $this->question_shuffle_answers = !isset($data['question_shuffle_answers']) || $data['question_shuffle_answers'] == 1;
        $this->round_duration_unit = $data['round_duration_unit'] ?? self::MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS;
        $this->round_duration_value = $data['round_duration_value'] ?? 7;
        $this->round_matches = $data['round_matches'] ?? 1;
        $this->rounds = $data['rounds'] ?? 10;
        $this->winner_mdl_user = $data['winner_mdl_user'] ?? 0;
        $this->winner_score = $data['winner_score'] ?? 0;
        $this->state = $data['state'] ?? self::STATE_UNPUBLISHED;
    }

    /**
     * Select all participants within the given course.
     * Important: this excludes teachers!
     *
     * @return stdClass[]
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function get_mdl_participants(bool $only_enabled) {
        $mdl_users = $this->get_mdl_users_and_teachers(true, false);
        if (!$only_enabled) {
            return $mdl_users;
        }

        return \array_filter($mdl_users, function (\stdClass $mdl_user) {
            $participant = util::get_user($mdl_user, $this->get_id());
            return $participant->is_enabled();
        });
    }

    /**
     * Select all teachers within the given course.
     * Important: this excludes regular users!
     *
     * @return stdClass[]
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function get_mdl_teachers() {
        return $this->get_mdl_users_and_teachers(false, true);
    }

    /**
     * Select users within the given course.
     *
     * @return stdClass[]
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    private function get_mdl_users_and_teachers(bool $include_users, bool $include_teachers) {
        list($course, $coursemodule) = get_course_and_cm_from_instance($this->get_id(), 'challenge');
        $ctx = $coursemodule->context;
        global $DB;
        $picture_fields = user_picture::fields('u');
        $sql = "SELECT DISTINCT $picture_fields
                FROM {user} u
                JOIN {role_assignments} a ON a.userid = u.id
                JOIN {context} ctx ON (a.contextid = ctx.id AND instanceid = :courseid)
                ORDER BY u.lastname, u.firstname ASC";
        $params = ['courseid' => $course->id];
        $users = $DB->get_records_sql($sql, $params);
        $result = [];
        foreach ($users as $user) {
            $teacher = util::user_has_capability(MOD_CHALLENGE_CAP_MANAGE, $ctx, $user->id);
            if ($include_users && $teacher) {
                continue;
            }
            if ($include_teachers && !$teacher) {
                continue;
            }
            $result[] = $user;
        }
        return $result;
    }

    /**
     * Loads all rounds of this game.
     *
     * @return round[]
     * @throws dml_exception
     */
    public function get_rounds() {
        global $DB;
        $sql_params = ['game' => $this->get_id()];
        $sql = "SELECT * 
                FROM {challenge_rounds}
                WHERE game = :game
                ORDER BY number ASC";
        $records = $DB->get_records_sql($sql, $sql_params);
        $result = [];
        foreach ($records as $round_data) {
            $round = new round();
            $round->apply($round_data);
            $result[] = $round;
        }
        return $result;
    }

    /**
     * Loads all pending and active rounds of this game and checks if they need to be started or stopped.
     *
     * @throws dml_exception
     */
    public function validate_rounds() {
        // get pending and active rounds
        global $DB;
        $sql_params = [
            'game' => $this->get_id(),
            'state_pending' => round::STATE_PENDING,
            'state_active' => round::STATE_ACTIVE
        ];
        $sql = "SELECT *
                FROM {challenge_rounds}
                WHERE game = :game
                AND (state = :state_pending OR state = :state_active)";
        $records = $DB->get_records_sql($sql, $sql_params);

        // check if round should be started or stopped
        foreach ($records as $round_data) {
            $round = new round();
            $round->apply($round_data);
            $this->validate_round($round);
        }
    }

    /**
     * Checks if the given round needs to be started or stopped.
     *
     * @param round $round
     */
    public function validate_round(round $round) {
        // check if round needs to be started
        if ($round->get_state() === round::STATE_PENDING && $round->is_started()) {
            try {
                $this->start_round($round);
            } catch (moodle_exception $ignored) {
            }
        }

        // check if matches need to be generated
        if ($round->are_next_matches_needed()) {
            try {
                $this->create_next_matches($round);
            } catch (moodle_exception $ignored) {
            }
        }

        // check if round needs to be ended
        if ($round->get_state() === round::STATE_ACTIVE && $round->is_ended()) {
            try {
                $this->stop_round($round);
                $this->disable_inactive_participants($round);
            } catch (moodle_exception $ignored) {
            }
        }
    }

    /**
     * Starts the given round, i.e.
     * - set start and end date
     * - set state to active
     * - select participants
     *
     * @param round $round
     *
     * @return round The upcoming round
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function start_round(round $round) {
        $round->set_timestart(time());
        $round->set_timeend(time() + $this->calculate_round_duration_seconds());
        $round->set_state(round::STATE_ACTIVE);
        $round->set_questions($this->get_question_count());
        $round->set_matches($this->get_round_matches());
        $round->save();
        return $round;
    }

    /**
     * Creates a set of matches for the given round.
     *
     * @param round $round
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function create_next_matches(round $round) {
        // get participants
        $mdl_users = $this->get_mdl_participants(true);
        \shuffle($mdl_users);

        // todo: filter outer participants who have an unfinished match!

        // create a set of matches
        $match_number = $round->get_matches_created() + 1;
        while (count($mdl_users) > 1) {
            $mdl_user_1 = array_shift($mdl_users);
            $mdl_user_2 = array_shift($mdl_users);
            $match = new match();
            $match->set_mdl_user_1($mdl_user_1->id);
            $match->set_mdl_user_2($mdl_user_2->id);
            $match->set_round($round->get_id());
            $match->set_number($match_number);
            $match->save();
        }

        // track number of created matches in round
        $round->set_matches_created($match_number);
        $round->save();
    }

    /**
     * Goes through the rounds before the provided round and disables all participants that were inactive during the last 2 finished rounds.
     * Does nothing if there are less than 2 finished rounds.
     * If there are unfinished, started rounds in between the last 2 finished rounds and the provided round, activity in those unfinished, started
     * rounds is counted as well.
     *
     * @param round $max_round
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function disable_inactive_participants(round $max_round) {
        // get previous (up to) 2 finished rounds + all started rounds between the last finished and the new max round
        // e.g. max_round = 10, then the result could be [6,7] as finished rounds and [8,9] as started but unfinished rounds
        // never pick more than 2 finished rounds.
        $rounds = \array_filter($this->get_rounds(), function(round $round) use ($max_round) {
            if (!$round->is_started()) {
                return false;
            }
            return $round->get_number() < $max_round->get_number();
        });
        $rounds = \array_reverse($rounds);
        $started_rounds = [];
        $finished_rounds = [];
        foreach($rounds as $round) {
            \assert($round instanceof round);
            if ($round->is_ended()) {
                $finished_rounds[] = $round;
            } else {
                $started_rounds[] = $round;
            }
            if (\count($finished_rounds) >= 2) {
                break;
            }
        }

        // skip if we didn't have at least 2 finished rounds
        if (count($finished_rounds) < 2) {
            return;
        }

        // load participant ids who had at least one attempt within the rounds from above
        $participant_ids = [];
        foreach(\array_merge($finished_rounds, $started_rounds) as $round) {
            \assert($round instanceof round);
            $attempts = $round->get_match_attempts();
            foreach ($attempts as $attempt) {
                \assert($attempt instanceof attempt);
                if (!$attempt->is_answered()) {
                    continue;
                }
                $participant_ids[] = $attempt->get_mdl_user();
            }
        }
        $participant_ids = \array_unique($participant_ids);

        // disable users who didn't have at least one attempt within the rounds from above
        $mdl_users = $this->get_mdl_participants(true);
        foreach ($mdl_users as $mdl_user) {
            $participant = util::get_user($mdl_user, $this->get_id());
            if (\array_search($participant->get_mdl_user(), $participant_ids) === false) {
                $participant->set_status(participant::STATUS_DISABLED);
                $participant->save();
            }
        }
    }

    /**
     * Schedules the given round.
     *
     * @param round $round
     * @param int $timestart
     * @param int $timeend
     *
     * @throws dml_exception
     */
    public function schedule_round(round $round, int $timestart, int $timeend) {
        $round->set_timestart($timestart);
        $round->set_timeend($timeend);
        $round->set_state(round::STATE_PENDING);
        $round->save();
    }

    /**
     * Ends the given round.
     *
     * @param round $round
     * @param bool $force
     *
     * @throws dml_exception
     */
    public function stop_round(round $round, bool $force = false) {
        // stop round
        if ($force || $round->get_timeend() === 0) {
            $round->set_timeend(time());
        }
        $round->set_state(round::STATE_FINISHED);
        $round->save();

        // close matches if necessary
        foreach ($round->get_match_entities() as $match) {
            try {
                $match->check_winner($this, $round);
            } catch (moodle_exception $ignored) {
            }
        }
    }

    /**
     * Loads all categories of this game.
     *
     * @return category[]
     * @throws dml_exception
     */
    public function get_categories() {
        global $DB;
        $sql_params = ['game' => $this->get_id()];
        $sql = "SELECT cat.*
                FROM {challenge_categories} cat
                INNER JOIN {challenge_rounds} rnd ON cat.round_first = rnd.id 
                WHERE cat.game = :game
                ORDER BY rnd.number ASC, cat.id ASC";
        $records = $DB->get_records_sql($sql, $sql_params);
        $result = [];
        foreach ($records as $category_data) {
            $category = new category();
            $category->apply($category_data);
            $result[] = $category;
        }
        return $result;
    }

    /**
     * Gets all categories that are active for the given $round_id.
     *
     * @param int $round_id
     * @return category[]
     * @throws dml_exception
     */
    public function get_categories_by_round($round_id) {
        $round_ids = array_map(function (round $round) {
            return $round->get_id();
        }, $this->get_rounds());
        $round_index = array_search($round_id, $round_ids);
        $result = [];
        foreach ($this->get_categories() as $category) {
            $round_index_first = array_search($category->get_round_first(), $round_ids);
            if ($round_index < $round_index_first) {
                continue;
            }
            $round_index_last = $category->get_round_last() ? array_search($category->get_round_last(), $round_ids) : PHP_INT_MAX;
            if ($round_index > $round_index_last) {
                continue;
            }
            $result[] = $category;
        }
        return $result;
    }

    /**
     * Loads all matches of this game the given user is involved in.
     *
     * @param int $mdl_user_id
     *
     * @return tournament[]
     * @throws dml_exception
     */
    public function get_user_matches($mdl_user_id) {
        global $DB;
        $sql_params = [
            'game' => $this->get_id(),
            'state_deleted' => round::STATE_DELETED,
            'user_1' => $mdl_user_id,
            'user_2' => $mdl_user_id
        ];
        $records = $DB->get_records_sql("SELECT m.* 
                    FROM {challenge_matches} m
                    INNER JOIN {challenge_rounds} r ON m.round=r.id 
                    WHERE r.game = :game 
                        AND r.state <> :state_deleted
                        AND (m.mdl_user_1 = :user_1 OR m.mdl_user_2 = :user_2) 
                    ORDER BY m.timecreated ASC", $sql_params);
        $result = [];
        foreach ($records as $match_data) {
            $match = new match();
            $match->apply($match_data);
            $result[] = $match;
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
        switch ($this->round_duration_unit) {
            case self::MOD_CHALLENGE_ROUND_DURATION_UNIT_HOURS:
                return 60 * 60;
            case self::MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS:
                return 60 * 60 * 24;
            case self::MOD_CHALLENGE_ROUND_DURATION_UNIT_WEEKS:
            default:
                return 60 * 60 * 24 * 7;
        }
    }

    /**
     * @return int
     */
    public function get_round_matches(): int {
        return $this->round_matches;
    }

    /**
     * @return int
     */
    public function get_number_of_rounds(): int {
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
