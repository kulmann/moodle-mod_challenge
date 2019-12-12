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

global $CFG;
require_once($CFG->dirroot . '/lib/questionlib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

class tournament_request_question extends external_api {

    /**
     * Definition of parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'matchid' => new external_value(PARAM_INT, 'match id'),
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
     * @param int $matchid
     * @param int $topicid
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid, $matchid, $topicid) {
        $params = ['coursemoduleid' => $coursemoduleid, 'matchid' => $matchid, 'topicid' => $topicid];
        self::validate_parameters(self::request_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE, $USER;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        $match = util::get_match($matchid);
        $topic = util::get_topic($topicid);
        $tournament = util::get_tournament($topic->get_tournament());
        util::validate_tournament($game, $tournament);
        if (intval($USER->id) !== $match->get_mdl_user_1() && intval($USER->id) !== $match->get_mdl_user_2()) {
            throw new invalid_parameter_exception("User is not allowed to fetch a question for this match.");
        }

        // get questions of this match
        $questions = $tournament->get_questions_by_topic_and_users($topicid, $match->get_mdl_user_1(), $match->get_mdl_user_2());
        foreach ($questions as $question) {
            util::check_question_timeout($question, $game);
        }

        // load question of the user or create a new one
        $question_of_user = self::get_question_owner_by_user(intval($USER->id), $questions);
        if ($question_of_user === null) {
            $question_of_user = new tournament_question();
            $question_of_user->set_topic($topicid);
            $question_of_user->set_mdl_user($USER->id);

            // if other user already had a question, pick the same one!
            if (empty($questions)) {
                $level = util::get_level($topic->get_level());
                $mdl_question = $level->get_random_question();
            } else {
                $question = \reset($questions);
                $mdl_question = \question_bank::load_question($question->get_mdl_question(), false);
            }
            $question_of_user->set_mdl_question($mdl_question->id);

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
            $question_of_user->set_mdl_answers_order(implode(",", $mdl_answer_ids));

            // save this question
            $question_of_user->save();
        }

        // create export
        $exporter = new tournament_question_dto($question_of_user, $tournament, $game, $ctx);
        return $exporter->export($renderer);
    }

    /**
     * Returns the question object that belongs to the given user, or null if none found.
     *
     * @param int $mdl_user
     * @param tournament_question[] $questions
     *
     * @return tournament_question | null
     */
    private static function get_question_owner_by_user($mdl_user, $questions) {
        foreach ($questions as $question) {
            if ($question->get_mdl_user() === $mdl_user) {
                return $question;
            }
        }
        return null;
    }
}
