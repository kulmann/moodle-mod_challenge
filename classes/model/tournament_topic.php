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
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tournament_topic extends abstract_model {

    /**
     * @var int The timestamp of the creation of this topic.
     */
    protected $timecreated;
    /**
     * @var int The timestamp of the last update of this topic.
     */
    protected $timemodified;
    /**
     * @var int The id of the tournament instance this topic belongs to.
     */
    protected $tournament;
    /**
     * @var int The step index within the tournament.
     */
    protected $step;
    /**
     * @var int The id of the assigned level.
     */
    protected $level;

    /**
     * tournament_topic constructor.
     */
    function __construct() {
        parent::__construct('challenge_tnmt_topics', 0);
        $this->timecreated = \time();
        $this->timemodified = \time();
        $this->tournament = 0;
        $this->step = 0;
        $this->level = 0;
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
        $this->level = $data['level'];
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
    public function get_level(): int {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function set_level(int $level) {
        $this->level = $level;
    }
}
