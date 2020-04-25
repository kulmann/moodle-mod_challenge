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
use mod_challenge\external\exporter\question_dto;
use mod_challenge\model\attempt;
use mod_challenge\model\question;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/questionlib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

class player_request_match_question extends external_api {

    /**
     * Definition of parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'matchid' => new external_value(PARAM_INT, 'match id'),
            'number' => new external_value(PARAM_INT, 'number of the question within the match')
        ]);
    }

    /**
     * Definition of return type for {@see request}.
     *
     * @return external_single_structure
     */
    public static function request_returns() {
        return question_dto::get_read_structure();
    }

    /**
     * Get an existing or create a new question for the logged in user for the given match.
     *
     * @param int $coursemoduleid
     * @param int $matchid
     * @param int $number
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid, $matchid, $number) {
        $params = ['coursemoduleid' => $coursemoduleid, 'matchid' => $matchid, 'number' => $number];
        self::validate_parameters(self::request_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE, $USER;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        $match = util::get_match($matchid);
        $round = util::get_round($match->get_round());
        if (intval($USER->id) !== $match->get_mdl_user_1() && intval($USER->id) !== $match->get_mdl_user_2()) {
            throw new invalid_parameter_exception("User is not allowed to fetch a question for this match.");
        }

        // get existing question
        $question = $match->get_question_by_number($number);

        // if question doesn't exist at all: create a new one
        if ($question === null) {
            // create question
            $question = new question();
            $question->set_matchid($matchid);
            $question->set_number($number);

            // set moodle question
            $active_categories = $game->get_categories_by_round($match->get_round());
            $mdl_question = $round->get_random_mdl_question($active_categories);
            $question->set_mdl_question($mdl_question->id);

            // fixate the answer order
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

            // done. save it
            $question->save();
        }

        // check if an attempt needs to be logged
        $attempt = util::get_attempt_by_question($question->get_id(), intval($USER->id));
        if ($attempt === null) {
            $attempt = new attempt();
            $attempt->set_question($question->get_id());
            $attempt->set_mdl_user(intval($USER->id));
            $attempt->save();
        }

        // create export
        $exporter = new question_dto($question, $match, $game, $ctx);
        return $exporter->export($renderer);
    }
}
