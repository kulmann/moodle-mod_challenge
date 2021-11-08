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
use external_value;
use invalid_parameter_exception;
use mod_challenge\external\exporter\match_dto;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die();

class admin_get_round_matches extends external_api {

    /**
     * Definition of parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'roundid' => new external_value(PARAM_INT, 'round id'),
        ]);
    }

    /**
     * @return external_multiple_structure
     */
    public static function request_returns() {
        return new external_multiple_structure(match_dto::get_read_structure());
    }

    /**
     * Get all matches of a round.
     *
     * @param int $coursemoduleid
     * @param int $roundid
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid, $roundid) {
        $params = ['coursemoduleid' => $coursemoduleid, 'roundid' => $roundid];
        self::validate_parameters(self::request_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        $game = util::get_game($coursemodule);
        $round = util::get_round($roundid);
        util::validate_round($game, $round);
        $matches = $round->get_match_entities();

        // construct result
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $result = [];
        foreach ($matches as $match) {
            $match_dto = new match_dto($match, $game, $ctx);
            $result[] = $match_dto->export($renderer);
        }
        return $result;
    }
}
