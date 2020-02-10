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
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_challenge\external\exporter\bool_dto;
use mod_challenge\external\exporter\tournament_topic_dto;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class admin_save_round extends external_api {

    /**
     * Definition of parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'round' => new external_multiple_structure(new external_function_parameters([
                'id' => new external_value(PARAM_INT, 'round id'),
                'name' => new external_value(PARAM_NOTAGS, 'round name'),
            ])),
            'categories' => new external_multiple_structure(new external_function_parameters([
                // TODO: describe category data
            ])),
            'matches' => new external_multiple_structure(new external_function_parameters([
                // TODO: describe match data
            ]))
        ]);
    }

    /**
     * @return external_single_structure
     */
    public static function request_returns() {
        return bool_dto::get_read_structure();
    }

    /**
     * Save all topics of a tournament.
     *
     * @param int $coursemoduleid
     * @param array $round
     * @param array $categories
     * @param array $matches
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid, $round, $categories, $matches) {
        $params = ['coursemoduleid' => $coursemoduleid, 'round' => $round, 'categories' => $categories, 'matches' => $matches];
        self::validate_parameters(self::request_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context(($ctx = $coursemodule->context));
        util::require_user_has_capability(CAP_CHALLENGE_MANAGE, $ctx);
        $game = util::get_game($coursemodule);
        // TODO: load and validate

        // create new topics
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        try {
            // TODO: implement all the saving...
        } catch (\invalid_state_exception $e) {
            $exporter = new bool_dto(false, $ctx);
            return $exporter->export($renderer);
        }
        $exporter = new bool_dto(true, $ctx);
        return $exporter->export($renderer);
    }
}
