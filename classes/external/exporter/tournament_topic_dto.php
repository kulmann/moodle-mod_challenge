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
use mod_challenge\model\tournament_topic;
use mod_challenge\util;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tournament_topic_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tournament_topic_dto extends exporter {

    /**
     * @var tournament_topic
     */
    protected $topic;
    /**
     * @var game
     */
    protected $game;

    /**
     * tournament_topic_dto constructor.
     *
     * @param tournament_topic $topic
     * @param game $game
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(tournament_topic $topic, game $game, context $context) {
        $this->topic = $topic;
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
            'level' => [
                'type' => PARAM_INT,
                'description' => 'id of the level',
            ],
            'level_name' => [
                'type' => PARAM_TEXT,
                'description' => 'name of the level',
            ],
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        $level = util::get_level($this->topic->get_level());
        return \array_merge(
            $this->topic->to_array(),
            [
                'level_name' => $level->get_name(),
            ]
        );
    }
}
