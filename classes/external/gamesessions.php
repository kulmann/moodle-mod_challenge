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
use mod_challenge\external\exporter\tournament_dto;
use mod_challenge\model\tournament_gamesession;
use mod_challenge\model\tournament_question;
use mod_challenge\util;
use moodle_exception;
use question_answer;
use restricted_context_exception;
use stdClass;
use function array_filter;
use function array_map;
use function array_pop;
use function assert;
use function implode;
use function shuffle;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Class gamesessions
 *
 * @package    mod_challenge\external
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gamesessions extends external_api {

    /**
     * Definition of parameters for {@see get_current_gamesession}.
     *
     * @return external_function_parameters
     */
    public static function get_current_gamesession_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
        ]);
    }

    /**
     * Definition of return type for {@see get_current_gamesession}.
     *
     * @return external_single_structure
     */
    public static function get_current_gamesession_returns() {
        return tournament_dto::get_read_structure();
    }

    /**
     * Get current gamesession.
     *
     * @param int $coursemoduleid
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function get_current_gamesession($coursemoduleid) {
        $params = ['coursemoduleid' => $coursemoduleid];
        self::validate_parameters(self::get_current_gamesession_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);

        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);

        // try to find existing in-progress gamesession or create a new one
        $gamesession = util::get_or_create_gamesession($game);
        $exporter = new tournament_dto($gamesession, $game, $ctx);
        return $exporter->export($renderer);
    }

    /**
     * Definition of parameters for {@see get_current_question}.
     *
     * @return external_function_parameters
     */
    public static function get_question_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'gamesessionid' => new external_value(PARAM_INT, 'game session id'),
            'levelid' => new external_value(PARAM_INT, 'id of the level'),
        ]);
    }

    /**
     * Definition of return type for {@see get_question}.
     *
     * @return external_single_structure
     */
    public static function get_question_returns() {
        return tournamentQuestion_dto::get_read_structure();
    }

    /**
     * Get current question. Selects a new one, if none is currently selected.
     *
     * @param int $coursemoduleid
     * @param int $gamesessionid
     * @param int $levelid
     *
     * @return stdClass
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function get_question($coursemoduleid, $gamesessionid, $levelid) {
        $params = [
            'coursemoduleid' => $coursemoduleid,
            'gamesessionid' => $gamesessionid,
            'levelid' => $levelid,
        ];
        self::validate_parameters(self::get_question_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);

        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);

        // try to find existing in-progress gamesession or create a new one
        $gamesession = util::get_or_create_gamesession($game);
        util::validate_gamesession($game, $gamesession);

        // grab the requested level
        $level = util::get_level($levelid);

        // get question or create a new one if necessary.
        $question = $gamesession->get_question_by_level($level->get_id());
        if (!$gamesession->is_level_finished($level->get_id()) && $question === null) {
            $question = new tournament_question();
            $question->set_gamesession($gamesessionid);
            $question->set_level($level->get_id());
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
        }

        // return
        $exporter = new tournamentQuestion_dto($question, $game, $ctx);
        return $exporter->export($renderer);
    }

    /**
     * Definition of parameters for {@see submit_answer}.
     *
     * @return external_function_parameters
     */
    public static function submit_answer_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'gamesessionid' => new external_value(PARAM_INT, 'game session id'),
            'questionid' => new external_value(PARAM_INT, 'question id'),
            'mdlanswerid' => new external_value(PARAM_INT, 'id of the selected moodle answer'),
        ]);
    }

    /**
     * Definition of return type for {@see submit_answer}.
     *
     * @return external_single_structure
     */
    public static function submit_answer_returns() {
        return tournamentQuestion_dto::get_read_structure();
    }

    /**
     * Applies the submitted answer to the question record in our DB.
     *
     * @param int $coursemoduleid
     * @param int $gamesessionid
     * @param int $questionid
     * @param int $mdlanswerid
     *
     * @return stdClass
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function submit_answer($coursemoduleid, $gamesessionid, $questionid, $mdlanswerid) {
        $params = [
            'coursemoduleid' => $coursemoduleid,
            'gamesessionid' => $gamesessionid,
            'questionid' => $questionid,
            'mdlanswerid' => $mdlanswerid,
        ];
        self::validate_parameters(self::submit_answer_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);

        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);

        // try to find existing in-progress gamesession
        $gamesession = util::get_gamesession($gamesessionid);
        if (!$gamesession->is_in_progress()) {
            throw new moodle_exception('gamesession is not available anymore.');
        }
        util::validate_gamesession($game, $gamesession);

        // get the question
        $question = util::get_question($questionid);
        util::validate_question($gamesession, $question);
        if ($question->is_finished()) {
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
        $question->set_mdl_answer_given($mdlanswerid);
        $question->set_finished(true);
        $question->set_correct($correct_mdl_answer->id == $mdlanswerid);
        $time_taken = (\time() - $question->get_timecreated());
        $time_available = util::calculate_available_time($game, $question);
        $time_remaining = \max(0, ($time_available - $time_taken));
        $question->set_timeremaining($time_remaining);
        if ($question->is_correct()) {
            $max_points = $game->get_question_duration();
            $points = \min($time_remaining, $max_points);
            $question->set_score($points);
        }
        $question->save();

        // update stats in the gamesession
        $gamesession->increment_answers_total();
        if ($question->is_correct()) {
            $gamesession->increase_score($question->get_score());
            $gamesession->increment_answers_correct();
        }
        if ($gamesession->get_answers_total() === $game->count_active_levels()) {
            $gamesession->set_state(tournament_gamesession::STATE_FINISHED);
        }
        $gamesession->save();

        // return result object
        $exporter = new tournamentQuestion_dto($question, $game, $ctx);
        return $exporter->export($renderer);
    }

    /**
     * Definition of parameters for {@see cancel_answer}.
     *
     * @return external_function_parameters
     */
    public static function cancel_answer_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'gamesessionid' => new external_value(PARAM_INT, 'game session id'),
            'questionid' => new external_value(PARAM_INT, 'question id'),
        ]);
    }

    /**
     * Definition of return type for {@see cancel_answer}.
     *
     * @return external_single_structure
     */
    public static function cancel_answer_returns() {
        return tournamentQuestion_dto::get_read_structure();
    }

    /**
     * Marks the question as cancelled (i.e. time ran out).
     *
     * @param int $coursemoduleid
     * @param int $gamesessionid
     * @param int $questionid
     *
     * @return stdClass
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function cancel_answer($coursemoduleid, $gamesessionid, $questionid) {
        $params = [
            'coursemoduleid' => $coursemoduleid,
            'gamesessionid' => $gamesessionid,
            'questionid' => $questionid,
        ];
        self::validate_parameters(self::cancel_answer_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);

        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);

        // try to find existing in-progress gamesession
        $gamesession = util::get_gamesession($gamesessionid);
        if (!$gamesession->is_in_progress()) {
            throw new moodle_exception('gamesession is not available anymore.');
        }
        util::validate_gamesession($game, $gamesession);

        // get the question
        $question = util::get_question($questionid);
        util::validate_question($gamesession, $question);
        if ($question->is_finished()) {
            throw new moodle_exception('question has already been answered.');
        }

        // set data on question
        $question->set_mdl_answer_given(0);
        $question->set_finished(true);
        $question->set_correct(false);
        $question->set_score(0);
        $time_taken = (\time() - $question->get_timecreated());
        $time_available = util::calculate_available_time($game, $question);
        $time_remaining = \max(0, ($time_available - $time_taken));
        $question->set_timeremaining($time_remaining);
        $question->save();

        // update stats in the gamesession
        $gamesession->increment_answers_total();
        if ($gamesession->get_answers_total() === $game->count_active_levels()) {
            $gamesession->set_state(tournament_gamesession::STATE_FINISHED);
        }
        $gamesession->save();

        // return result object
        $exporter = new tournamentQuestion_dto($question, $game, $ctx);
        return $exporter->export($renderer);
    }
}
