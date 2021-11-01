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

defined('MOODLE_INTERNAL') || die();

/**
 * Class tournament_topic
 *
 * @package    mod_challenge\model
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class round extends abstract_model {

    const STATE_PENDING = "pending";
    const STATE_ACTIVE = "active";
    const STATE_DELETED = "deleted";
    const STATE_FINISHED = "finished";
    const VALID_STATES = [
        self::STATE_PENDING,
        self::STATE_ACTIVE,
        self::STATE_DELETED,
        self::STATE_FINISHED,
    ];

    /**
     * @var int The id of the game instance this round belongs to.
     */
    protected $game;
    /**
     * @var int The number of this round within the game (1-based).
     */
    protected $number;
    /**
     * @var int The timestamp of when this round will start.
     */
    protected $timestart;
    /**
     * @var int The timestamp of when this round will end.
     */
    protected $timeend;
    /**
     * @var string Current state of the round. Defaults to `pending`.
     */
    protected $state;
    /**
     * @var string The name of the round.
     */
    protected $name;
    /**
     * @var int The max. number of matches this round will have.
     */
    protected $matches;
    /**
     * @var int The number of already created within this round.
     */
    protected $matches_created;
    /**
     * @var int The number of questions this round requires.
     */
    protected $questions;

    /**
     * round constructor.
     */
    function __construct() {
        parent::__construct('challenge_rounds', 0);
        $this->game = 0;
        $this->number = 1;
        $this->timestart = 0;
        $this->timeend = 0;
        $this->state = self::STATE_PENDING;
        $this->name = '';
        $this->matches = 1;
        $this->matches_created = 0;
        $this->questions = 0;
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
        $this->game = $data['game'];
        $this->number = $data['number'] ?? 1;
        $this->timestart = $data['timestart'] ?? 0;
        $this->timeend = $data['timeend'] ?? 0;
        $this->state = $data['state'] ?? self::STATE_PENDING;
        $this->name = $data['name'] ?? '';
        $this->matches = $data['matches'] ?? 1;
        $this->matches_created = $data['matches_created'] ?? 0;
        $this->questions = $data['questions'] ?? 0;
    }

    /**
     * Checks if the round start date is reached.
     *
     * @return bool
     */
    public function is_started(): bool {
        return $this->get_timestart() !== 0 && $this->get_timestart() <= time();
    }

    /**
     * Checks if the round end date is reached.
     *
     * @return bool
     */
    public function is_ended(): bool {
        return $this->get_timeend() !== 0 && $this->get_timeend() <= time();
    }

    /**
     * Returns one random question out of the categories that are assigned to this level.
     *
     * @param category[] $active_categories
     * @param int[] $mdl_user_ids Ids of moodle users participating in this question
     *
     * @return \question_definition
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public function get_random_mdl_question($active_categories, $mdl_user_ids = []): \question_definition {
        global $DB;

        // collect all moodle question categories
        $mdl_category_ids = [];
        foreach ($active_categories as $category) {
            $mdl_category_ids = \array_merge($mdl_category_ids, $category->get_mdl_category_ids());
        }
        list($cat_sql, $cat_params) = $DB->get_in_or_equal($mdl_category_ids);

        // collect all moodle questions which are available for selection
        $sql = "
            SELECT q.id
              FROM {question} q 
        INNER JOIN {qtype_multichoice_options} qmo ON q.id=qmo.questionid
             WHERE q.qtype = ? AND qmo.single = ? AND q.category $cat_sql 
        ";
        $params = \array_merge(["multichoice", 1], $cat_params);
        $available_ids = $DB->get_records_sql($sql, $params);
        if (empty($available_ids)) {
            throw new \dml_exception('no question available');
        }

        // collect all questions the given users were involved in so far
        $used_questions = [];
        if (!empty($mdl_user_ids)) {
            $sql_questions = "
       SELECT DISTINCT cq.mdl_question, cq.timecreated
                  FROM {challenge_questions} cq
            INNER JOIN {challenge_matches} cm ON cq.matchid=cm.id
                 WHERE cm.mdl_user_1 IN (?) OR cm.mdl_user_2 IN (?)
            ";
            $params = [implode(',', $mdl_user_ids), implode(',', $mdl_user_ids)];
            $used_questions = $DB->get_records_sql($sql_questions, $params);
        }

        // Shuffle here because SQL RAND() can't be used.
        shuffle($available_ids);

        // if there are used questions, prefer the unused ones
        if (!empty($used_questions)) {
            $used_questions_map = [];
            foreach ($used_questions as $q) {
                $used_questions_map[$q->mdl_question] = $q->timecreated;
            }
            usort($available_ids, function ($qid1, $qid2) use ($used_questions_map) {
                if (isset($used_questions_map[$qid1]) && isset($used_questions_map[$qid2])) {
                    return $used_questions_map[$qid1] <=> $used_questions_map[$qid2];
                }
                if (isset($used_questions_map[$qid1])) {
                    return 1;
                }
                if (isset($used_questions_map[$qid2])) {
                    return -1;
                }
                return 0;
            });
        }

        // Take the first one in the array.
        $id = \reset($available_ids)->id;
        return \question_bank::load_question($id, false);
    }

    /**
     * Checks if next set of matches needs to be generated.
     *
     * @return bool
     */
    public function are_next_matches_needed(): bool {
        if ($this->is_ended()) {
            return false;
        }

        if ($this->get_matches_created() >= $this->get_matches()) {
            return false;
        }

        // for now matches are evenly distributed across the round runtime. Check if next timeslot is reached.
        $round_duration = $this->timeend - $this->timestart;
        $match_duration = $round_duration / $this->get_matches();
        $time_passed = min(\time(), $this->timeend) - $this->timestart;
        $reached_match_starts = \ceil($time_passed / $match_duration);
        return $this->get_matches_created() < $reached_match_starts;
    }

    /**
     * Loads all matches of this round.
     *
     * @return match[]
     * @throws \dml_exception
     */
    public function get_match_entities(): array {
        global $DB;
        $records = $DB->get_records('challenge_matches', ['round' => $this->get_id()]);
        return \array_map(function ($record) {
            $match = new match();
            $match->apply($record);
            return $match;
        }, $records);
    }

    /**
     * Loads all questions of this round, identified through matches.
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_match_questions(): array {
        global $DB;
        $sql = "
                SELECT question.*
                  FROM {challenge_questions} question
            INNER JOIN {challenge_matches} m ON question.matchid=m.id 
                 WHERE m.round = :round
              ORDER BY m.id, question.number ASC
        ";
        $sql_conditions = ['round' => $this->get_id()];
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
     * Loads all attempts of this round, identified through matches.
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_match_attempts(): array {
        global $DB;
        $sql = "
                SELECT attempt.*
                  FROM {challenge_attempts} attempt
            INNER JOIN {challenge_questions} q ON attempt.question=q.id 
            INNER JOIN {challenge_matches} m ON q.matchid=m.id 
                 WHERE m.round = :round
              ORDER BY m.id, q.number, attempt.id ASC
        ";
        $sql_conditions = ['round' => $this->get_id()];
        $records = $DB->get_records_sql($sql, $sql_conditions);
        $result = [];
        foreach ($records as $record) {
            $attempt = new attempt();
            $attempt->apply($record);
            $result[] = $attempt;
        }
        return $result;
    }

    /**
     * @return int
     */
    public function get_game(): int {
        return $this->game;
    }

    /**
     * @param int $game
     */
    public function set_game(int $game) {
        $this->game = $game;
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
    public function get_timestart(): int {
        return $this->timestart;
    }

    /**
     * @param int $timestart
     */
    public function set_timestart(int $timestart) {
        $this->timestart = $timestart;
    }

    /**
     * @return int
     */
    public function get_timeend(): int {
        return $this->timeend;
    }

    /**
     * @param int $timeend
     */
    public function set_timeend(int $timeend) {
        $this->timeend = $timeend;
    }

    /**
     * @return string
     */
    public function get_state(): string {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function set_state(string $state) {
        if (in_array($state, self::VALID_STATES)) {
            $this->state = $state;
        }
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
    public function get_matches(): int {
        return $this->matches ?? 1;
    }

    /**
     * @param int $matches
     */
    public function set_matches(int $matches) {
        $this->matches = $matches;
    }

    /**
     * @return int
     */
    public function get_matches_created(): int {
        return $this->matches_created;
    }

    /**
     * @param int $matches_created
     */
    public function set_matches_created(int $matches_created) {
        $this->matches_created = $matches_created;
    }

    /**
     * @return int
     */
    public function get_questions(): int {
        return $this->questions;
    }

    /**
     * @param int $questions
     */
    public function set_questions(int $questions) {
        $this->questions = $questions;
    }
}
