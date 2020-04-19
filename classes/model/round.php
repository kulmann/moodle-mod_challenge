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
     * @var int The number of this round within the game.
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
     * @var int The number of questions this round requires.
     */
    protected $questions;

    /**
     * round constructor.
     */
    function __construct() {
        parent::__construct('challenge_rounds', 0);
        $this->game = 0;
        $this->number = 0;
        $this->timestart = 0;
        $this->timeend = 0;
        $this->state = self::STATE_PENDING;
        $this->name = '';
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
        $this->id = isset($data['id']) ? $data['id'] : 0;
        $this->game = $data['game'];
        $this->number = isset($data['number']) ? $data['number'] : 0;
        $this->timestart = isset($data['timestart']) ? $data['timestart'] : 0;
        $this->timeend = isset($data['timeend']) ? $data['timeend'] : 0;
        $this->state = isset($data['state']) ? $data['state'] : self::STATE_PENDING;
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->questions = isset($data['questions'])  ? $data['questions'] : 0;
    }

    /**
     * Returns one random question out of the categories that are assigned to this level.
     *
     * @param category[] $active_categories
     *
     * @return \question_definition
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public function get_random_mdl_question($active_categories): \question_definition {
        global $DB;

        // collect all moodle question categories
        $mdl_category_ids = [];
        foreach($active_categories as $category) {
            $mdl_category_ids = \array_merge($mdl_category_ids, $category->get_mdl_category_ids());
        }
        list($cat_sql, $cat_params) = $DB->get_in_or_equal($mdl_category_ids);

        // build query for moodle question selection
        $sql = "
            SELECT q.id
              FROM {question} q 
        INNER JOIN {qtype_multichoice_options} qmo ON q.id=qmo.questionid
             WHERE q.qtype = ? AND qmo.single = ? AND q.category $cat_sql 
        ";
        $params = \array_merge(["multichoice", 1], $cat_params);

        // Get all available questions.
        $available_ids = $DB->get_records_sql($sql, $params);
        if (!empty($available_ids)) {
            // Shuffle here because SQL RAND() can't be used.
            shuffle($available_ids);
            // Take the first one in the array.
            $id = \reset($available_ids)->id;
            return \question_bank::load_question($id, false);
        } else {
            throw new \dml_exception('no question available');
        }
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
    public function set_timeend(int $timeend): void {
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
    public function set_state(string $state): void {
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
    public function set_name(string $name): void {
        $this->name = $name;
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
    public function set_questions(int $questions): void {
        $this->questions = $questions;
    }
}
