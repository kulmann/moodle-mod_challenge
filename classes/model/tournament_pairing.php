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
 * Class tournament_pairing
 *
 * @package    mod_challenge\model
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tournament_pairing extends abstract_model {

    const WINNER_OPEN = 'open';
    const WINNER_TIE = 'tie';
    const WINNER_P1 = 'p1';
    const WINNER_P2 = 'p2';
    const WINNERS = [
        self::WINNER_OPEN,
        self::WINNER_TIE,
        self::WINNER_P1,
        self::WINNER_P2,
    ];

    /**
     * @var int The timestamp of the creation of this pairing.
     */
    protected $timecreated;
    /**
     * @var int The timestamp of the last update of this pairing.
     */
    protected $timemodified;
    /**
     * @var int The id of the tournament instance this pairing belongs to.
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
     * @var string The state of this pairing, out of [open, tie, p1, p2].
     */
    protected $winner;

    /**
     * tournament_pairing constructor.
     */
    function __construct() {
        parent::__construct('challenge_tnmt_pairings', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->tournament = 0;
        $this->step = 0;
        $this->mdl_user_1 = 0;
        $this->mdl_user_2 = 0;
        $this->winner = self::WINNER_OPEN;
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
        $this->winner = isset($data['winner']) ? $data['winner'] : self::WINNER_OPEN;
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
     * @return string
     */
    public function get_winner(): string {
        return $this->winner;
    }

    /**
     * Marks that none of the players won.
     */
    public function set_winner_tie() {
        $this->set_winner(self::WINNER_TIE);
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
     * @param string $winner
     */
    public function set_winner(string $winner) {
        if (\in_array($winner, self::WINNERS)) {
            $this->winner = $winner;
        }
    }

    /**
     * Returns whether this pairing is already finished.
     *
     * @return bool
     */
    public function is_finished() {
        return $this->winner !== self::WINNER_OPEN;
    }
}
