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
use mod_challenge\model\category;
use mod_challenge\model\round;
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
            'roundid' => new external_value(PARAM_INT, 'round id', false),
            'name' => new external_value(PARAM_NOTAGS, 'round name', false),
            'addedcategories' => new external_multiple_structure(new external_function_parameters([
                'categoryid' => new external_value(PARAM_INT, 'category id'),
                'mdlcategory' => new external_value(PARAM_INT, 'moodle question category id'),
                'subcategories' => new external_value(PARAM_BOOL, 'whether or not subcategories are included')
            ])),
            'deletedcategories' => new external_multiple_structure(new external_function_parameters([
                'categoryid' => new external_value(PARAM_INT, 'category id'),
                'mdlcategory' => new external_value(PARAM_INT, 'moodle question category id'),
                'subcategories' => new external_value(PARAM_BOOL, 'whether or not subcategories are included')
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
     * Save a round
     *
     * @param int $coursemoduleid
     * @param int $roundid
     * @param string $name
     * @param array $addedcategories
     * @param array $deletedcategories
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function request($coursemoduleid, $roundid, $name, $addedcategories, $deletedcategories) {
        $params = ['coursemoduleid' => $coursemoduleid, 'roundid' => $roundid, 'name' => $name, 'addedcategories' => $addedcategories, 'deletedcategories' => $deletedcategories];
        self::validate_parameters(self::request_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context(($ctx = $coursemodule->context));
        util::require_user_has_capability(MOD_CHALLENGE_CAP_MANAGE, $ctx);
        $game = util::get_game($coursemodule);
        $existingRounds = $game->get_rounds();

        // save round and categories
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        try {
            if ($roundid === 0) {
                $round = new round();
                $round->set_game($game->get_id());
                $round->set_number(count($existingRounds) + 1);
            } else {
                $round = util::get_round($roundid);
                util::validate_round($game, $round);
            }

            // set round core data
            $round->set_name($name);
            $round->save();

            // add new categories
            foreach ($addedcategories as $categoryData) {
                $category = new category();
                $category->set_game($game->get_id());
                $category->set_includes_subcategories($categoryData['subcategories']);
                $category->set_round_first($round->get_id());
                $category->set_mdl_category($categoryData['mdlcategory']);
                $category->save();
            }

            // delete removed categories
            foreach ($deletedcategories as $categoryData) {
                $category = util::get_category($categoryData['categoryid']);
                util::validate_category($game, $category);
                if ($category->get_round_first() === $round->get_id()) {
                    $category->delete();
                } else {
                    $prevRound = admin_save_round::find_previous_round($round, $existingRounds);
                    if ($prevRound !== false) {
                        $category->set_round_last($prevRound->get_id());
                        $category->save();
                    }
                }
            }
        } catch (\invalid_state_exception $e) {
            $exporter = new bool_dto(false, $ctx);
            return $exporter->export($renderer);
        }
        $exporter = new bool_dto(true, $ctx);
        return $exporter->export($renderer);
    }

    /**
     * Finds the predecessor of the given $round within the given $existingRounds. Returns null if it's the first round.
     *
     * @param round $round
     * @param round[] $existingRounds
     * @return round | null
     */
    private static function find_previous_round($round, $existingRounds) {
        $previous = null;
        foreach($existingRounds as $r) {
            if ($r->get_id() === $round->get_id()) {
                break;
            }
            $previous = $r;
        }
        return $previous;
    }
}
