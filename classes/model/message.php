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
class message extends abstract_model {

    const TYPE_MATCH_STARTED = 'match_started';
    const TYPE_MATCH_STALE = 'match_stale';
    const TYPE_MATCH_FINISHED = 'match_finished';
    const TYPE_OPPONENT_PLAYED = 'opponent_played';
    const VALID_TYPES = [
        self::TYPE_MATCH_STARTED,
        self::TYPE_MATCH_STALE,
        self::TYPE_MATCH_FINISHED,
        self::TYPE_OPPONENT_PLAYED,
    ];

    const STATUS_PENDING = "pending";
    const STATUS_PROGRESS = "progress";
    const STATUS_SENT = "sent";
    const VALID_STATUS = [
        self::STATUS_PENDING,
        self::STATUS_PROGRESS,
        self::STATUS_SENT,
    ];

    /**
     * @var int Timestamp of creation of this message.
     */
    protected $timecreated;
    /**
     * @var int The id of the game instance this message belongs to.
     */
    protected $game;
    /**
     * @var int The id of the round instance this message belongs to.
     */
    protected $round;
    /**
     * @var int The id of the match instance this message belongs to.
     */
    protected $matchid;
    /**
     * @var int The id of the moodle user who will receive this message.
     */
    protected $mdl_user;
    /**
     * @var string The type of this message. See constants at the top of this class for available types. See `VALID_TYPES` for valid types.
     */
    protected $type;
    /**
     * @var string The processing state of this message. See `VALID_STATES` for valid states.
     */
    protected $status;

    /**
     * message constructor.
     */
    function __construct() {
        parent::__construct('challenge_messages', 0);
        $this->timecreated = 0;
        $this->game = 0;
        $this->round = 0;
        $this->matchid = 0;
        $this->mdl_user = 0;
        $this->type = '';
        $this->status = self::STATUS_PENDING;
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
        $this->timecreated = isset($data['timecreated']) ? $data['timecreated'] : 0;
        $this->game = $data['game'];
        $this->round = isset($data['round']) ? $data['round'] : 0;
        $this->matchid = isset($data['matchid']) ? $data['matchid'] : 0;
        $this->mdl_user = isset($data['mdl_user']) ? $data['mdl_user'] : 0;
        $this->type = isset($data['type']) ? $data['type'] : '';
        $this->status = isset($data['status']) ? $data['status'] : self::STATUS_PENDING;
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
    public function get_round(): int {
        return $this->round;
    }

    /**
     * @param int $round
     */
    public function set_round(int $round) {
        $this->round = $round;
    }

    /**
     * @return int
     */
    public function get_matchid(): int {
        return $this->matchid;
    }

    /**
     * @param int $matchid
     */
    public function set_matchid(int $matchid) {
        $this->matchid = $matchid;
    }

    /**
     * @return int
     */
    public function get_mdl_user(): int {
        return $this->mdl_user;
    }

    /**
     * @param int $mdl_user
     */
    public function set_mdl_user(int $mdl_user) {
        $this->mdl_user = $mdl_user;
    }

    /**
     * @return string
     */
    public function get_type(): string {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function set_type(string $type) {
        if (in_array($type, self::VALID_TYPES)) {
            $this->type = $type;
        }
    }

    /**
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function set_status(string $status) {
        if (in_array($status, self::VALID_STATUS)) {
            $this->status = $status;
        }
    }
}
