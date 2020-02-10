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

global $CFG;
require_once($CFG->libdir . '/questionlib.php');

use coding_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_challenge\external\exporter\mdl_question_dto;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class main_get_mdl_question
 *
 * @package    mod_challenge\external
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main_get_mdl_question extends external_api {

    /**
     * Defines parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'questionid' => new external_value(PARAM_INT, 'the id of the question')
        ]);
    }

    /**
     * Defines return type for {@see request}.
     *
     * @return external_single_structure
     */
    public static function request_returns() {
        return mdl_question_dto::get_read_structure();
    }

    /**
     * Gets data of a specific moodle question.
     *
     * @param int $coursemoduleid
     * @param int $questionid
     *
     * @return stdClass
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid, $questionid) {
        $params = ['coursemoduleid' => $coursemoduleid, 'questionid' => $questionid];
        self::validate_parameters(self::request_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);

        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;

        // load the moodle question
        $question = util::get_question($questionid);
        $mdl_question = $question->get_mdl_question_ref();

        $exporter = new mdl_question_dto($mdl_question, $ctx);
        return $exporter->export($renderer);
    }
}
