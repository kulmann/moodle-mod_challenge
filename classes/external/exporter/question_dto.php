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
use mod_challenge\model\question;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tournament_question_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_dto extends exporter {

    /**
     * @var question
     */
    protected $question;
    /**
     * @var match
     */
    protected $match;
    /**
     * @var game
     */
    protected $game;

    /**
     * tournament_question_dto constructor.
     *
     * @param question $question
     * @param match $match
     * @param game $game
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(question $question, match $match, game $game, context $context) {
        $this->question = $question;
        $this->match = $match;
        $this->game = $game;
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
                'description' => 'timestamp when this question was created',
            ],
            'timemodified' => [
                'type' => PARAM_INT,
                'description' => 'timestamp when this question was modified',
            ],
            'matchid' => [
                'type' => PARAM_INT,
                'description' => 'id of the match this question was chosen from',
            ],
            'number' => [
                'type' => PARAM_INT,
                'description' => 'position within the question set of the match',
            ],
            'mdl_question' => [
                'type' => PARAM_INT,
                'description' => 'id of the moodle question instance',
            ],
            'mdl_user_winner' => [
                'type' => PARAM_INT,
                'description' => 'id of the moodle user who won this question',
            ],
            'mdl_user_1' => [
                'type' => PARAM_INT,
                'description' => 'id of the first moodle user this question was chosen for',
            ],
            'mdl_user_1_timestart' => [
                'type' => PARAM_INT,
                'description' => 'timestamp of when the first moodle user started this question',
            ],
            'mdl_user_1_answer' => [
                'type' => PARAM_INT,
                'description' => 'id of the moodle answer the first user has chosen',
            ],
            'mdl_user_1_score' => [
                'type' => PARAM_INT,
                'description' => 'score the first user has reached by answering this question',
            ],
            'mdl_user_1_correct' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the question was answered correctly by the first user',
            ],
            'mdl_user_1_finished' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the question was answered at all by the first user',
            ],
            'mdl_user_2' => [
                'type' => PARAM_INT,
                'description' => 'id of the second moodle user this question was chosen for',
            ],
            'mdl_user_2_timestart' => [
                'type' => PARAM_INT,
                'description' => 'timestamp of when the second moodle user started this question',
            ],
            'mdl_user_2_answer' => [
                'type' => PARAM_INT,
                'description' => 'id of the moodle answer the second user has chosen',
            ],
            'mdl_user_2_score' => [
                'type' => PARAM_INT,
                'description' => 'score the second user has reached by answering this question',
            ],
            'mdl_user_2_correct' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the question was answered correctly by the second user',
            ],
            'mdl_user_2_finished' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not the question was answered at all by the second user',
            ],
            // custom (non-model) fields
            'mdl_question_id' => [
                'type' => PARAM_INT,
                'description' => 'id of the associated moodle question',
            ],
            'mdl_question_type' => [
                'type' => PARAM_TEXT,
                'description' => 'type of the associated moodle question',
            ],
            'score_max' => [
                'type' => PARAM_INT,
                'description' => 'the max score you can get for answering this question correct.',
            ],
            'time_max' => [
                'type' => PARAM_INT,
                'description' => 'the max time for answering this questions',
            ],
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        $mdl_question = $this->question->get_mdl_question_ref();
        return \array_merge(
            $this->question->to_array(),
            [
                'mdl_question_id' => $mdl_question->id,
                'mdl_question_type' => \get_class($mdl_question),
                'score_max' => $this->game->get_question_duration(),
                'time_max' => $this->game->get_question_duration(),
            ]
        );
    }
}
