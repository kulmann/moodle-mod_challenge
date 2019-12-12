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

namespace mod_challenge\external;

use coding_exception;
use dml_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_challenge\external\exporter\tournament_question_dto;
use mod_challenge\model\tournament_question;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class tournament_request_question extends external_api {

    /**
     * Definition of parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'topicid' => new external_value(PARAM_INT, 'topic id'),
        ]);
    }

    /**
     * Definition of return type for {@see request}.
     *
     * @return external_single_structure
     */
    public static function request_returns() {
        return tournament_question_dto::get_read_structure();
    }

    /**
     * Get an existing or create a new question for the logged in user for the given topic.
     *
     * @param int $coursemoduleid
     * @param int $topicid
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid, $topicid) {
        $params = ['coursemoduleid' => $coursemoduleid, 'topicid' => $topicid];
        self::validate_parameters(self::request_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE, $USER;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        $topic = util::get_topic($topicid);
        $tournament = util::get_tournament($topic->get_tournament());
        util::validate_tournament($game, $tournament);

        // grab the requested level
        $level = util::get_level($topic->get_level());

        // get question or create a new one if necessary.
        $question = $tournament->get_question_by_user_and_topic(intval($USER->id), $topicid);
        if ($question === null) {
            $question = new tournament_question();
            $question->set_topic($topicid);
            $question->set_mdl_user($USER->id);
            $mdl_question = $level->get_random_question();
            $question->set_mdl_question($mdl_question->id);
            $mdl_answer_ids = array_map(
                function ($mdl_answer) {
                    return $mdl_answer->id;
                },
                $mdl_question->answers
            );
            if ($game->is_question_shuffle_answers()) {
                shuffle($mdl_answer_ids);
            }
            $question->set_mdl_answers_order(implode(",", $mdl_answer_ids));
            $question->save();
        } else {
            util::check_question_timeout($question, $game);
        }

        // create export
        $exporter = new tournament_question_dto($question, $tournament, $game, $ctx);
        return $exporter->export($renderer);
    }
}
