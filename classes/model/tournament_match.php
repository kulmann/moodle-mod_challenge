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
 * Class tournament_match
 *
 * @package    mod_challenge\model
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tournament_match extends abstract_model {

    /**
     * @var int The timestamp of the creation of this match.
     */
    protected $timecreated;
    /**
     * @var int The timestamp of the last update of this match.
     */
    protected $timemodified;
    /**
     * @var int The id of the tournament instance this match belongs to.
     */
    protected $tournament;
    /**
     * @var int The step index within the tournament.
     */
    protected $step;
    /**
     * @var int The id of the first user.
     */
    protected $mdl_user_1;
    /**
     * @var int The id of the second user.
     */
    protected $mdl_user_2;
    /**
     * @var int The user id of the winner of this match.
     */
    protected $mdl_user_winner;

    /**
     * tournament_match constructor.
     */
    function __construct() {
        parent::__construct('challenge_tnmt_matches', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->tournament = 0;
        $this->step = 0;
        $this->mdl_user_1 = 0;
        $this->mdl_user_2 = 0;
        $this->mdl_user_winner = 0;
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
        $this->tournament = $data['tournament'];
        $this->step = $data['step'];
        $this->mdl_user_1 = $data['mdl_user_1'];
        $this->mdl_user_2 = $data['mdl_user_2'];
        $this->mdl_user_winner = isset($data['mdl_user_winner']) ? $data['mdl_user_winner'] : 0;
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
    public function get_tournament(): int {
        return $this->tournament;
    }

    /**
     * @param int $tournament
     */
    public function set_tournament(int $tournament) {
        $this->tournament = $tournament;
    }

    /**
     * @return int
     */
    public function get_step(): int {
        return $this->step;
    }

    /**
     * @param int $step
     */
    public function set_step(int $step) {
        $this->step = $step;
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
    public function set_mdl_user_1(int $mdl_user_1) {
        $this->mdl_user_1 = $mdl_user_1;
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
    public function set_mdl_user_2(int $mdl_user_2) {
        $this->mdl_user_2 = $mdl_user_2;
    }

    /**
     * @return int
     */
    public function get_mdl_user_winner(): int {
        return $this->mdl_user_winner;
    }

    /**
     * Marks the first player as winner.
     */
    public function set_winner_player1() {
        $this->set_winner(self::WINNER_P1);
    }

    /**
     * Marks the second player as winner.
     */
    public function set_winner_player2() {
        $this->set_winner(self::WINNER_P2);
    }

    /**
     * @param int $mdl_user_winner
     */
    public function set_mdl_user_winner(int $mdl_user_winner) {
        $this->mdl_user_winner = $mdl_user_winner;
    }

    /**
     * Returns whether this match is already finished.
     *
     * @return bool
     */
    public function is_finished() {
        return $this->mdl_user_winner > 0;
    }
}