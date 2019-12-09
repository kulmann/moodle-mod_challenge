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
use mod_challenge\external\exporter\tournament_dto;
use mod_challenge\external\exporter\tournament_pairing_dto;
use mod_challenge\model\tournament;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tournaments
 *
 * @package    mod_challenge\external
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tournaments extends external_api {

    /**
     * Definition of parameters for {@see get_tournaments}.
     *
     * @return external_function_parameters
     */
    public static function get_tournaments_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
        ]);
    }

    /**
     * Definition of return type for {@see get_tournaments}.
     *
     * @return external_multiple_structure
     */
    public static function get_tournaments_returns() {
        return new external_multiple_structure(
            tournament_dto::get_read_structure()
        );
    }

    /**
     * Get all tournaments of a game.
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
    public static function get_tournaments($coursemoduleid) {
        $params = ['coursemoduleid' => $coursemoduleid];
        self::validate_parameters(self::get_tournaments_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        util::require_user_has_capability('mod/challenge:manage', $ctx);

        // collect export data
        $result = [];
        $tournaments = $game->get_tournaments();
        foreach ($tournaments as $tournament) {
            $exporter = new tournament_dto($tournament, $game, $ctx);
            $result[] = $exporter->export($renderer);
        }
        return $result;
    }

    /**
     * Definition of parameters for {@see get_user_tournaments}.
     *
     * @return external_function_parameters
     */
    public static function get_user_tournaments_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
        ]);
    }

    /**
     * Definition of return type for {@see get_user_tournaments}.
     *
     * @return external_multiple_structure
     */
    public static function get_user_tournaments_returns() {
        return new external_multiple_structure(
            tournament_dto::get_read_structure()
        );
    }

    /**
     * Get all tournaments of a game mat
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
    public static function get_user_tournaments($coursemoduleid) {
        $params = ['coursemoduleid' => $coursemoduleid];
        self::validate_parameters(self::get_user_tournaments_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);

        // load context
        global $PAGE, $USER;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);

        // collect export data
        $result = [];
        $tournaments = $game->get_user_tournaments($USER->id);
        foreach ($tournaments as $tournament) {
            $exporter = new tournament_dto($tournament, $game, $ctx);
            $result[] = $exporter->export($renderer);
        }
        return $result;
    }

    /**
     * Definition of parameters for {@see set_tournament_state}.
     *
     * @return external_function_parameters
     */
    public static function set_tournament_state_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'tournamentid' => new external_value(PARAM_INT, 'the id of the tournament which is about to get a new state'),
            'state' => new external_value(PARAM_ALPHA, 'the new state of the tournament'),
        ]);
    }

    /**
     * Definition of return type for {@see set_tournament_state}.
     *
     * @return external_single_structure
     */
    public static function set_tournament_state_returns() {
        return bool_dto::get_read_structure();
    }

    /**
     * Sets the state for a tournament.
     *
     * @param int $coursemoduleid
     * @param int $tournamentid
     * @param string $state
     *
     * @return stdClass
     * @throws \required_capability_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function set_tournament_state($coursemoduleid, $tournamentid, $state) {
        $params = ['coursemoduleid' => $coursemoduleid, 'tournamentid' => $tournamentid, 'state' => $state];
        self::validate_parameters(self::set_tournament_state_parameters(), $params);
        util::validate_tournament_state($state);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        util::require_user_has_capability('mod/challenge:manage', $ctx);

        // get the tournament for validation
        $tournament = util::get_tournament($tournamentid);
        util::validate_tournament($game, $tournament);

        // set the new state
        $tournament->set_state($state);
        $tournament->save();

        // return success status
        $exporter = new bool_dto(true, $ctx);
        return $exporter->export($renderer);
    }

    /**
     * Definition of parameters for {@see save_tournament}.
     *
     * @return external_function_parameters
     */
    public static function save_tournament_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'tournamentid' => new external_value(PARAM_INT, 'the id of the tournament'),
            'name' => new external_value(PARAM_TEXT, 'name of the tournament'),
        ]);
    }

    /**
     * Definition of return type for {@see save_tournament}.
     *
     * @return external_single_structure
     */
    public static function save_tournament_returns() {
        return bool_dto::get_read_structure();
    }

    /**
     * Updates or inserts the given data as a tournament.
     *
     * @param int $coursemoduleid
     * @param int $tournamentid
     * @param string $name
     *
     * @return stdClass
     * @throws \required_capability_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function save_tournament($coursemoduleid, $tournamentid, $name) {
        $params = [
            'coursemoduleid' => $coursemoduleid,
            'tournamentid' => $tournamentid,
            'name' => $name,
        ];
        self::validate_parameters(self::save_tournament_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        util::require_user_has_capability('mod/challenge:manage', $ctx);

        // get the tournament or create one
        if ($tournamentid) {
            $tournament = util::get_tournament($tournamentid);
            util::validate_tournament($game, $tournament);
        } else {
            $tournament = new tournament();
            $tournament->set_game($game->get_id());
        }

        // set the data for the tournament
        $tournament->set_name($name);
        $tournament->save();

        // return success status
        $exporter = new bool_dto(true, $ctx);
        return $exporter->export($renderer);
    }

    /**
     * Definition of parameters for {@see get_tournament_pairings}.
     *
     * @return external_function_parameters
     */
    public static function get_tournament_pairings_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'tournamentid' => new external_value(PARAM_INT, 'the id of the tournament'),
            'step' => new external_value(PARAM_INT, 'optional: step in the tournament progress', false),
        ]);
    }

    /**
     * Definition of return type for {@see get_tournament_pairings}.
     *
     * @return external_multiple_structure
     */
    public static function get_tournament_pairings_returns() {
        return new external_multiple_structure(tournament_pairing_dto::get_read_structure());
    }

    /**
     * Get all pairings of the given tournament.
     *
     * @param int $coursemoduleid
     * @param int $tournamentid
     * @param int $step
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function get_tournament_pairings($coursemoduleid, $tournamentid, $step = 0) {
        $params = ['coursemoduleid' => $coursemoduleid, 'tournamentid' => $tournamentid, 'step' => $step];
        self::validate_parameters(self::get_tournament_pairings_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);

        // load tournament
        $tournament = util::get_tournament($tournamentid);
        util::validate_tournament($game, $tournament);

        // load pairings
        $pairings = $tournament->get_pairings($step);
        $result = [];
        foreach($pairings as $pairing) {
            $exporter = new tournament_pairing_dto($pairing, $game, $ctx);
            $result[] = $exporter->export($renderer);
        }
        return $result;
    }

    /**
     * Definition of parameters for {@see save_tournament_pairings}.
     *
     * @return external_function_parameters
     */
    public static function save_tournament_pairings_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'tournamentid' => new external_value(PARAM_INT, 'the id of the tournament'),
            'pairings' => new external_multiple_structure(new external_single_structure([
                'mdl_user_1' => new external_value(PARAM_INT, 'the moodle user id of the first user'),
                'mdl_user_2' => new external_value(PARAM_INT, 'the moodle user id of the second user'),
            ]))
        ]);
    }

    /**
     * Definition of return type for {@see save_tournament_pairings}.
     *
     * @return external_single_structure
     */
    public static function save_tournament_pairings_returns() {
        return bool_dto::get_read_structure();
    }

    /**
     * Clears and then inserts the participant pairings for the given tournament.
     *
     * @param int $coursemoduleid
     * @param int $tournamentid
     * @param array $pairings
     *
     * @return stdClass
     * @throws \required_capability_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function save_tournament_pairings($coursemoduleid, $tournamentid, $pairings) {
        $params = [
            'coursemoduleid' => $coursemoduleid,
            'tournamentid' => $tournamentid,
            'pairings' => $pairings,
        ];
        self::validate_parameters(self::save_tournament_pairings_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        util::require_user_has_capability('mod/challenge:manage', $ctx);

        // load tournament
        $tournament = util::get_tournament($tournamentid);
        util::validate_tournament($game, $tournament);

        // create new pairings
        try {
            $tournament->clear_pairings();
            $tournament->create_pairings($pairings);
        } catch(\invalid_state_exception $e) {
            $exporter = new bool_dto(false, $ctx);
            return $exporter->export($renderer);
        }
        $exporter = new bool_dto(true, $ctx);
        return $exporter->export($renderer);
    }
}
