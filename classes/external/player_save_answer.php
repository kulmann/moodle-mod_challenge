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
use mod_challenge\util;
use moodle_exception;
use question_answer;
use restricted_context_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');

class player_save_answer extends external_api {

    /**
     * Definition of parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'questionid' => new external_value(PARAM_INT, 'question id'),
            'mdlanswerid' => new external_value(PARAM_INT, 'id of the selected moodle answer'),
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
     * Submits the given answer to the question.
     *
     * @param int $coursemoduleid
     * @param int $questionid
     * @param int $mdlanswerid
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid, $questionid, $mdlanswerid) {
        $params = ['coursemoduleid' => $coursemoduleid, 'questionid' => $questionid, 'mdlanswerid' => $mdlanswerid];
        self::validate_parameters(self::request_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE, $USER;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        $question = util::get_question($questionid);
        util::validate_question(intval($USER->id), $question);
        $match = util::get_match($question->get_matchid());

        // some validations
        if ($question->is_finished_by(intval($USER->id))) {
            throw new moodle_exception('question has already been answered.');
        }
        $mdl_question = $question->get_mdl_question_ref();
        if (!property_exists($mdl_question, 'answers')) {
            throw new coding_exception('property »answers« doesn\'t exist on the moodle question with id ' . $question->get_mdl_question() . '.');
        }

        // submit the answer
        $correct_mdl_answers = array_filter(
            $mdl_question->answers,
            function (question_answer $mdlanswer) {
                return $mdlanswer->fraction == 1;
            }
        );
        if (count($correct_mdl_answers) !== 1) {
            throw new moodle_exception('The moodle question with id ' . $question->get_mdl_question() . ' seems to be inapplicable for this activity.');
        }
        $correct_mdl_answer = array_pop($correct_mdl_answers);
        assert($correct_mdl_answer instanceof question_answer);
        $answered_correct = intval($correct_mdl_answer->id) === $mdlanswerid;
        if ($question->is_mdl_user_1(intval($USER->id))) {
            $question->set_mdl_user_1_answer($mdlanswerid);
            $question->set_mdl_user_1_finished(true);
            $question->set_mdl_user_1_correct($answered_correct);
            if ($answered_correct) {
                $time_taken = (time() - $question->get_mdl_user_1_timestart());
                $time_available = $game->get_question_duration();
                $question->set_mdl_user_1_score(max(0, min($time_available, ($time_available - $time_taken))));
            }
        } else {
            $question->set_mdl_user_2_answer($mdlanswerid);
            $question->set_mdl_user_2_finished(true);
            $question->set_mdl_user_2_correct($answered_correct);
            if ($answered_correct) {
                $time_taken = (time() - $question->get_mdl_user_2_timestart());
                $time_available = $game->get_question_duration();
                $question->set_mdl_user_2_score(max(0, min($time_available, ($time_available - $time_taken))));
            }
        }
        $question->save();

        // create export
        $exporter = new question_dto($question, $match, $game, $ctx);
        return $exporter->export($renderer);
    }
}
