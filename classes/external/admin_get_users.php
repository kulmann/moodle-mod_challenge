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
use mod_challenge\external\exporter\user_dto;
use mod_challenge\model\participant;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die();

class admin_get_users extends external_api {

    /**
     * Definition of parameters for {@see request}.
     *
     * @return external_function_parameters
     */
    public static function request_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
        ]);
    }

    /**
     * @return external_multiple_structure
     */
    public static function request_returns() {
        return new external_multiple_structure(user_dto::get_read_structure());
    }

    /**
     * Get all users that can be involved in the given game.
     *
     * @param int $coursemoduleid
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid) {
        $params = ['coursemoduleid' => $coursemoduleid];
        self::validate_parameters(self::request_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        $game = util::get_game($coursemodule);
        $participants = $game->get_mdl_participants();
        $teachers = $game->get_mdl_teachers();

        // construct result
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $result = [];
        foreach($teachers as $teacher) {
            $user_dto = new user_dto($game->get_id(), user_dto::TYPE_TEACHER, util::get_user($teacher), $ctx);
            $result[] = $user_dto->export($renderer);
        }
        foreach($participants as $participant) {
            $user_dto = new user_dto($game->get_id(), user_dto::TYPE_PARTICIPANT, util::get_user($participant), $ctx);
            $result[] = $user_dto->export($renderer);
        }
        return $result;
    }
}
