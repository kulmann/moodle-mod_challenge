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
use mod_challenge\external\exporter\bool_dto;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class admin_stop_round extends external_api {

    /**
     * Definition of parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'roundid' => new external_value(PARAM_INT, 'round id')
        ]);
    }

    /**
     * @return external_single_structure
     */
    public static function request_returns() {
        return bool_dto::get_read_structure();
    }

    /**
     * Stop the current round.
     *
     * @param int $coursemoduleid
     * @param int $roundid
     *
     * @return stdClass
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
        self::validate_context(($ctx = $coursemodule->context));
        util::require_user_has_capability(MOD_CHALLENGE_CAP_MANAGE, $ctx);
        $game = util::get_game($coursemodule);
        $round = util::get_round($roundid);
        util::validate_round($game, $round);
        util::validate_round_running($round);

        // start renderer
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');

        // trigger stopping current round
        $game->stop_round($round, true);
        $game->disable_inactive_participants($round);

        // return success response
        $exporter = new bool_dto(true, $ctx);
        return $exporter->export($renderer);
    }
}
