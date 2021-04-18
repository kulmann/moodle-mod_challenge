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
use mod_challenge\external\exporter\score_dto;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class main_get_scores
 *
 * @package    mod_challenge\external
 * @copyright  2021 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main_get_scores extends external_api {

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
     * Definition of return type for {@see request}.
     *
     * @return external_multiple_structure
     */
    public static function request_returns() {
        return new external_multiple_structure(
            score_dto::get_read_structure()
        );
    }

    /**
     * Get scores among all users.
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

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);

        global $PAGE, $DB;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);

        // collect score per match, including some additional data which is needed for the output
        $sql_scores = "SELECT CONCAT(a.mdl_user, '-', q.matchid) AS group_val, a.mdl_user, SUM(a.score) AS score_match, CONCAT(u.firstname, ' ', u.lastname) AS mdl_user_name
                         FROM {challenge_attempts} AS a
                   INNER JOIN {challenge_questions} AS q ON a.question=q.id
                   INNER JOIN {challenge_matches} AS m ON q.matchid=m.id
                   INNER JOIN {challenge_rounds} AS r ON m.round=r.id
                   INNER JOIN {user} AS u ON a.mdl_user=u.id
                        WHERE r.game = :game
                     GROUP BY group_val";
        $params_scores = ['game' => $game->get_id()];
        $records_scores = $DB->get_records_sql($sql_scores, $params_scores);

        $result = [];
        if ($records_scores) {
            // merge score per match into total score per user
            $scores_by_user = [];
            foreach ($records_scores as $record) {
                if (!isset($scores_by_user[$record->mdl_user])) {
                    $scores_by_user[$record->mdl_user] = ['score_sum' => 0, 'score_best' => 0, 'mdl_user' => $record->mdl_user, 'mdl_user_name' => $record->mdl_user_name];
                }
                $scores_by_user[$record->mdl_user]['score_sum'] += intval($record->score_match);
                $scores_by_user[$record->mdl_user]['score_best'] = max($scores_by_user[$record->mdl_user]['score_best'], intval($record->score_match));
            }

            // sort descending by score_sum and assign ranks
            $scores = array_values($scores_by_user);
            usort($scores, function ($s1, $s2) {
                // sort descending by inverting the comparison order
                return $s2['score_sum'] <=> $s1['score_sum'];
            });

            // create score_dto
            $rank = 1;
            foreach ($scores as $score) {
                $dto = new score_dto($rank++, $score['score_sum'], 0, $score['score_best'], 0, 0, $score['mdl_user'], $score['mdl_user_name'], $ctx);
                $result[] = $dto->export($renderer);
            }
        }
        return $result;
    }
}
