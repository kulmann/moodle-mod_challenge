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

namespace mod_challenge\external\exporter;

use context;
use core\external\exporter;
use mod_challenge\model\game;
use mod_challenge\model\round;
use renderer_base;
use function array_merge;

defined('MOODLE_INTERNAL') || die();

/**
 * Class round_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class round_dto extends exporter {

    /**
     * @var round
     */
    protected $round;
    /**
     * @var game
     */
    protected $game;

    /**
     * round_dto constructor.
     *
     * @param round $round
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(round $round, game $game, context $context) {
        $this->round = $round;
        $this->game = $game;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'category id',
            ],
            'game' => [
                'type' => PARAM_INT,
                'description' => 'game id',
            ],
            'number' => [
                'type' => PARAM_INT,
                'description' => 'number of this round within the game',
            ],
            'timestart' => [
                'type' => PARAM_INT,
                'description' => 'time when this round starts',
            ],
            'timeend' => [
                'type' => PARAM_INT,
                'description' => 'time when this round ends (calculated from start + duration)',
            ],
            'finished' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not this round is already finished (timeend < now)',
            ],
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        $timeend = $this->round->get_timestart() + $this->game->calculate_round_duration_seconds();
        return array_merge(
            $this->round->to_array(),
            [
                'timeend' => $timeend,
                'finished' => $timeend < time(),
            ]
        );
    }
}
