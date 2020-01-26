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
     * PLEASE NOTE: The end of it has to be calculated with the round duration from the game instance.
     */
    protected $timestart;

    /**
     * round constructor.
     */
    function __construct() {
        parent::__construct('challenge_rounds', 0);
        $this->game = 0;
        $this->number = 0;
        $this->timestart = 0;
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
}
