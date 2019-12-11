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
use invalid_state_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tournament
 *
 * @package    mod_challenge\model
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tournament extends abstract_model {

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
     * @var int The timestamp of the creation of this tournament.
     */
    protected $timecreated;
    /**
     * @var int The timestamp of the last update of this tournament.
     */
    protected $timemodified;
    /**
     * @var int The id of the challenge instance this level belongs to.
     */
    protected $game;
    /**
     * @var string State out of [finished, dumped, progress]
     */
    protected $state;
    /**
     * @var string Name of the tournament.
     */
    protected $name;
    /**
     * @var int The id of the user who won this tournament.
     */
    protected $winner_mdl_user;
    /**
     * @var int The score of the winning user.
     */
    protected $winner_score;

    /**
     * tournament constructor.
     */
    function __construct() {
        parent::__construct('challenge_tournaments', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->game = 0;
        $this->state = self::STATE_UNPUBLISHED;
        $this->name = '';
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
        $this->game = $data['game'];
        $this->state = isset($data['state']) ? $data['state'] : self::STATE_UNPUBLISHED;
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->winner_mdl_user = isset($data['winner_mdl_user']) ? $data['winner_mdl_user'] : 0;
        $this->winner_score = isset($data['winner_score']) ? $data['winner_score'] : 0;
    }

    /**
     * Clears the participant matches of an unpublished tournament.
     *
     * @return bool Whether clearing the matches was successful.
     * @throws dml_exception
     * @throws invalid_state_exception If the state is not 'unpublished' anymore.
     */
    public function clear_matches() {
        if ($this->get_state() !== self::STATE_UNPUBLISHED) {
            throw new invalid_state_exception("it's not allowed to clear the participant matches of a published tournament.");
        }
        global $DB;
        $conditions = ['tournament' => $this->get_id()];
        return $DB->delete_records('challenge_tnmt_matches', $conditions);
    }

    /**
     * Creates new matches for the given match data and saves them in the DB.
     *
     * @param array $matches_data
     *
     * @throws dml_exception
     * @throws invalid_state_exception
     */
    public function create_matches($matches_data) {
        if ($this->get_state() !== self::STATE_UNPUBLISHED) {
            throw new invalid_state_exception("it's not allowed to create fresh participant matches for a published tournament.");
        }
        foreach ($matches_data as $match_data) {
            $match = new tournament_match();
            $match->set_tournament($this->get_id());
            $match->set_step(0);
            $match->set_mdl_user_1($match_data['mdl_user_1']);
            $match->set_mdl_user_2($match_data['mdl_user_2']);
            $match->save();
        }
    }

    /**
     * Checks if this tournament already has matches.
     *
     * @return bool
     * @throws dml_exception
     */
    public function has_matches() {
        global $DB;
        $count_matches = $DB->count_records('challenge_tnmt_matches', ['tournament' => $this->get_id()]);
        return $count_matches > 0;
    }

    /**
     * Loads all matches of this tournament.
     *
     * @return tournament_match[]
     * @throws dml_exception
     */
    public function get_matches() {
        global $DB;
        $sql_conditions = ['tournament' => $this->get_id()];
        $records = $DB->get_records('challenge_tnmt_matches', $sql_conditions);
        $result = [];
        foreach ($records as $match_data) {
            $match = new tournament_match();
            $match->apply($match_data);
            $result[] = $match;
        }
        return $result;
    }

    /**
     * Loads all matches of this tournament where the given mdl_user is involved.
     *
     * @param int $mdl_user
     *
     * @return tournament_match[]
     * @throws dml_exception
     */
    public function get_user_matches($mdl_user) {
        global $DB;
        $sql = "SELECT * 
                  FROM {challenge_tnmt_matches} 
                 WHERE tournament = :tournament AND (mdl_user_1 = :user1 OR mdl_user_2 = :user2)
              ORDER BY step ASC";
        $records = $DB->get_records_sql($sql, ['tournament' => $this->get_id(), 'user1' => $mdl_user, 'user2' => $mdl_user]);
        $result = [];
        foreach ($records as $record) {
            $match = new tournament_match();
            $match->apply($record);
            $result[] = $match;
        }
        return $result;
    }

    /**
     * Clears the topics of an unpublished tournament.
     *
     * @return bool
     * @throws dml_exception
     * @throws invalid_state_exception
     */
    public function clear_topics() {
        if ($this->get_state() !== self::STATE_UNPUBLISHED) {
            throw new invalid_state_exception("it's not allowed to clear the topics of a published tournament.");
        }
        global $DB;
        $conditions = ['tournament' => $this->get_id()];
        return $DB->delete_records('challenge_tnmt_topics', $conditions);
    }

    /**
     * Creates new topics for the given topics data and saves them in the DB.
     *
     * @param array $topics_data
     *
     * @throws invalid_state_exception
     * @throws dml_exception
     */
    public function create_topics($topics_data) {
        if ($this->get_state() !== self::STATE_UNPUBLISHED) {
            throw new invalid_state_exception("it's not allowed to clear the topics of a published tournament.");
        }
        foreach ($topics_data as $topic_data) {
            if ($topic_data['level'] == 0) {
                continue;
            }
            $topic = new tournament_topic();
            $topic->apply($topic_data);
            $topic->set_tournament($this->get_id());
            $topic->save();
        }
    }

    /**
     * Checks if this tournament already has topics.
     *
     * @return bool
     * @throws dml_exception
     */
    public function has_topics() {
        global $DB;
        $count_topics = $DB->count_records('challenge_tnmt_topics', ['tournament' => $this->get_id()]);
        return $count_topics > 0;
    }

    /**
     * Loads all topics of this tournament.
     *
     * @return tournament_topic[]
     * @throws dml_exception
     */
    public function get_topics() {
        global $DB;
        $sql_conditions = ['tournament' => $this->get_id()];
        $records = $DB->get_records('challenge_tnmt_topics', $sql_conditions);
        $result = [];
        foreach ($records as $topic_data) {
            $topic = new tournament_topic();
            $topic->apply($topic_data);
            $result[] = $topic;
        }
        return $result;
    }

    /**
     * Counts the number of participants of a tournament.
     *
     * @return int
     * @throws dml_exception
     */
    public function count_participants() {
        global $DB;
        $count_matches = $DB->count_records('challenge_tnmt_matches', ['tournament' => $this->get_id(), 'step' => 0]);
        return $count_matches * 2;
    }

    /**
     * Calculates the number of game rounds / steps until all matches are done and a winner is determined.
     *
     * @return int
     * @throws dml_exception
     */
    public function get_number_of_steps() {
        $number_of_participants = $this->count_participants();
        return $number_of_participants - 1;
    }

    /**
     * Gets all questions ever created for this tournament.
     *
     * @return tournament_question[]
     * @throws dml_exception
     */
    public function get_questions() {
        global $DB;
        $sql_conditions = ['tournament' => $this->get_id()];
        $sql = "
            SELECT questions.*
              FROM {challenge_tnmt_questions} questions
        INNER JOIN {challenge_tnmt_topics} topics ON questions.topic = topics.id 
             WHERE topics.tournament = :tournament
          ORDER BY questions.timecreated DESC
        ";
        $records = $DB->get_records_sql($sql, $sql_conditions);
        $result = [];
        foreach ($records as $record) {
            $question = new tournament_question();
            $question->apply($record);
            $result[] = $question;
        }
        return $result;
    }

    /**
     * Gets a question for the given user and topic, if it exists. Returns null otherwise.
     *
     * @param int $mdl_user_id
     * @param int $topic_id
     *
     * @return tournament_question|null
     * @throws dml_exception
     */
    public function get_question_by_user_and_topic($mdl_user_id, $topic_id) {
        global $DB;
        $records = $DB->get_records('challenge_tnmt_questions', ['topic' => $topic_id, 'mdl_user' => $mdl_user_id]);
        if (empty($records)) {
            return null;
        } else {
            $question = new tournament_question();
            $question->apply($records[0]);
            return $question;
        }
    }

    /**
     * @return int
     */
    public function get_timecreated(): int {
        return $this->timecreated;
    }

    /**
     * @param int $timecreated
     */
    public function set_timecreated(int $timecreated) {
        $this->timecreated = $timecreated;
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
     * @return string
     */
    public function get_state(): string {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function set_state(string $state) {
        $this->state = $state;
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
