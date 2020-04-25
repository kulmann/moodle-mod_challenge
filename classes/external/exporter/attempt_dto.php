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
use mod_challenge\model\attempt;
use mod_challenge\model\game;
use mod_challenge\model\match;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class attempt_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_dto extends exporter {

    /**
     * @var attempt
     */
    protected $attempt;

    /**
     * attempt_dto constructor.
     *
     * @param attempt $attempt
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(attempt $attempt, context $context) {
        $this->attempt = $attempt;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'question id',
            ],
            'timecreated' => [
                'type' => PARAM_INT,
                'description' => 'timestamp when this attempt was created',
            ],
            'timemodified' => [
                'type' => PARAM_INT,
                'description' => 'timestamp when this attempt was modified',
            ],
            'question' => [
                'type' => PARAM_INT,
                'description' => 'id of the question this attempt was started for',
            ],
            'mdl_user' => [
                'type' => PARAM_INT,
                'description' => 'id of the moodle user who attempted to answer the question',
            ],
            'mdl_answer' => [
                'type' => PARAM_INT,
                'description' => 'id of the moodle answer the user has chosen',
            ],
            'score' => [
                'type' => PARAM_INT,
                'description' => 'score the user has reached by answering the question',
            ],
            'correct' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the question was answered correctly by the user',
            ],
            'finished' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the question was answered at all by the user',
            ],
            'timeremaining' => [
                'type' => PARAM_INT,
                'description' => 'the number of seconds remaining for answering the question after it was finished'
            ]
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        return $this->attempt->to_array();
    }
}
