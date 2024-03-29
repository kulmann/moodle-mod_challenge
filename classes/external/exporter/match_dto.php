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
use mod_challenge\model\match;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tournament_match_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class match_dto extends exporter {

    /**
     * @var match
     */
    protected $match;
    /**
     * @var game
     */
    protected $game;

    /**
     * tournament_match_dto constructor.
     *
     * @param match $match
     * @param game $game
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(match $match, game $game, context $context) {
        $this->match = $match;
        $this->game = $game;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'match id',
            ],
            'timecreated' => [
                'type' => PARAM_INT,
                'description' => 'timestamp of the creation of the match',
            ],
            'timemodified' => [
                'type' => PARAM_INT,
                'description' => 'timestamp of the last modification of the match',
            ],
            'round' => [
                'type' => PARAM_INT,
                'description' => 'id of the game round this match takes place in',
            ],
            'number' => [
                'type' => PARAM_INT,
                'description' => 'the number of this match within its round',
            ],
            'completed' => [
                'type' => PARAM_BOOL,
                'description' => 'whether the match is completed (finished by both participants or ended)',
            ],
            'mdl_user_1' => [
                'type' => PARAM_INT,
                'description' => 'first moodle user of the match',
            ],
            'mdl_user_1_completed' => [
                'type' => PARAM_BOOL,
                'description' => 'whether the first user has answered their questions for the match',
            ],
            'mdl_user_2' => [
                'type' => PARAM_INT,
                'description' => 'second moodle user of the match',
            ],
            'mdl_user_2_completed' => [
                'type' => PARAM_BOOL,
                'description' => 'whether the second user has answered their questions for the match',
            ],
            'winner_mdl_user' => [
                'type' => PARAM_INT,
                'description' => 'id of the moodle user who won this match',
            ],
            'winner_score' => [
                'type' => PARAM_INT,
                'description' => 'score of the moodle user who won this match',
            ],
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        // make sure, logged in user is always represented as the first one
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
                'winner_mdl_user' => $this->match->get_winner_score() > 0 ? $this->match->get_winner_mdl_user() : 0,
            ]
        );
    }
}
