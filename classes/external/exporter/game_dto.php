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
use mod_challenge\util;
use renderer_base;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class game_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class game_dto extends exporter {

    /**
     * @var game
     */
    protected $game;
    /**
     * @var stdClass
     */
    protected $user;
    /**
     * @var context
     */
    protected $ctx;

    /**
     * game_dto constructor.
     *
     * @param game $game
     * @param stdClass $user
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(game $game, stdClass $user, context $context) {
        $this->game = $game;
        $this->user = $user;
        $this->ctx = $context;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'gamesession id',
            ],
            'name' => [
                'type' => PARAM_TEXT,
                'description' => 'activity title',
            ],
            'question_count' => [
                'type' => PARAM_INT,
                'description' => 'the number of questions per tournament round'
            ],
            'question_duration' => [
                'type' => PARAM_INT,
                'description' => 'the number of seconds a user has for answering a question.',
            ],
            'review_duration' => [
                'type' => PARAM_INT,
                'description' => 'the number of seconds until after the question is answered the game goes back to the level overview',
            ],
            'round_duration_unit' => [
                'type' => PARAM_ALPHA,
                'description' => 'the time unit for the round duration',
            ],
            'round_duration_value' => [
                'type' => PARAM_INT,
                'description' => 'the amount for round duration, use in combination with duration unit',
            ],
            'rounds' => [
                'type' => PARAM_INT,
                'description' => 'the number of rounds for this game. 0 for infinite / stopping with course end.',
            ],
            'winner_mdl_user' => [
                'type' => PARAM_INT,
                'description' => 'the id of the moodle user who won this game',
            ],
            'winner_score' => [
                'type' => PARAM_INT,
                'description' => 'the score of the winning user',
            ],
            'state' => [
                'type' => PARAM_ALPHA,
                'description' => 'state of this game',
            ],
            // custom (non-model) fields
            'mdl_user' => [
                'type' => PARAM_INT,
                'description' => 'the id of the currently active moodle user',
            ],
            'mdl_user_teacher' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the logged in user has game editing capabilities',
            ],
            'round_duration_seconds' => [
                'type' => PARAM_INT,
                'description' => 'the number of seconds resulting from the round duration unit and value',
            ],
            'cap' => [
                'type' => PARAM_NOTAGS,
                'description' => 'capability check string'
            ]
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        return \array_merge(
            $this->game->to_array(),
            [
                'mdl_user' => $this->user->id,
                'mdl_user_teacher' => util::user_has_capability(MOD_CHALLENGE_CAP_MANAGE, $this->ctx, $this->user->id),
                'round_duration_seconds' => $this->game->calculate_round_duration_seconds(),
                'cap' => MOD_CHALLENGE_CAP_MANAGE
            ]
        );
    }
}
