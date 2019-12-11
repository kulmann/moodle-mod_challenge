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
use mod_challenge\model\tournament;
use mod_challenge\model\tournament_match;
use mod_challenge\model\tournament_gamesession;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tournament_match_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tournament_match_dto extends exporter {

    /**
     * @var tournament_match
     */
    protected $match;
    /**
     * @var game
     */
    protected $game;

    /**
     * tournament_match_dto constructor.
     *
     * @param tournament_match $match
     * @param game $game
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(tournament_match $match, game $game, context $context) {
        $this->match = $match;
        $this->game = $game;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'gamesession id',
            ],
            'timecreated' => [
                'type' => PARAM_INT,
                'description' => 'timestamp of the creation of the tournament',
            ],
            'timemodified' => [
                'type' => PARAM_INT,
                'description' => 'timestamp of the last modification of the tournament',
            ],
            'tournament' => [
                'type' => PARAM_INT,
                'description' => 'id of the tournament',
            ],
            'step' => [
                'type' => PARAM_INT,
                'description' => 'step index within the tournament',
            ],
            'mdl_user_1' => [
                'type' => PARAM_INT,
                'description' => 'first moodle user of the match',
            ],
            'mdl_user_2' => [
                'type' => PARAM_INT,
                'description' => 'second moodle user of the match',
            ],
            'mdl_user_winner' => [
                'type' => PARAM_INT,
                'description' => 'moodle user who won this match',
            ],
            'open' => [
                'type' => PARAM_BOOL,
                'description' => 'whether this match is still open or ongoing',
            ]
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        // make sure, logged in user is always the first one
        $mdl_user_1 = $this->match->get_mdl_user_1();
        $mdl_user_2 = $this->match->get_mdl_user_2();
        global $USER;
        if (intval($USER->id) === $mdl_user_2) {
            $this->match->set_mdl_user_1($mdl_user_2);
            $this->match->set_mdl_user_2($mdl_user_1);
        }
        // return data
        return \array_merge(
            $this->match->to_array(),
            [
                'open' => $this->match->get_mdl_user_winner() === 0,
            ]
        );
    }
}
