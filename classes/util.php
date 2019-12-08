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

namespace mod_challenge;

use cm_info;
use dml_exception;
use invalid_parameter_exception;
use mod_challenge\model\game;
use mod_challenge\model\tournament;
use mod_challenge\model\tournament_gamesession;
use mod_challenge\model\level;
use mod_challenge\model\tournament_question;

class util {

    /**
     * Checks if the logged in user has the given $capability.
     *
     * @param string $capability
     * @param \context $context
     * @param int|null $userid
     *
     * @return bool
     * @throws \coding_exception
     */
    public static function user_has_capability(string $capability, \context $context, $userid = null): bool {
        return \has_capability($capability, $context, $userid);
    }

    /**
     * Kills the current request if the logged in user doesn't have the required capabilities.
     *
     * @param string $capability
     * @param \context $context
     * @param int|null $userid
     *
     * @return void
     * @throws \required_capability_exception
     */
    public static function require_user_has_capability(string $capability, \context $context, $userid = null) {
        \require_capability($capability, $context, $userid);
    }

    /**
     * Checks that the gamesession belongs to the given $game and the logged in $USER.
     *
     * @param game $game
     * @param tournament_gamesession $gamesession
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_gamesession(game $game, tournament_gamesession $gamesession) {
        if ($game->get_id() !== $gamesession->get_game()) {
            throw new invalid_parameter_exception("gamesession " . $gamesession->get_id() . " doesn't belong to game " . $game->get_id());
        }
        global $USER;
        if ($gamesession->get_mdl_user() != $USER->id) {
            throw new invalid_parameter_exception("gamesession " . $gamesession->get_id() . " doesn't belong to logged in user");
        }
    }

    /**
     * Checks that the question belongs to the given $gamesession.
     *
     * @param tournament_gamesession $gamesession
     * @param tournament_question $question
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_question(tournament_gamesession $gamesession, tournament_question $question) {
        if ($gamesession->get_id() !== $question->get_gamesession()) {
            throw new invalid_parameter_exception("question " . $question->get_id() . " doesn't belong to given gamesession");
        }
    }

    /**
     * Checks that the level belongs to the given $game.
     *
     * @param game $game
     * @param level $level
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_level(game $game, level $level) {
        if ($game->get_id() !== $level->get_game()) {
            throw new invalid_parameter_exception("level " . $level->get_id() . " doesn't belong to given game");
        }
    }

    /**
     * Checks that the tournament belongs to the given $game.
     *
     * @param game $game
     * @param tournament $tournament
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_tournament(game $game, tournament $tournament) {
        if ($game->get_id() !== $tournament->get_game()) {
            throw new invalid_parameter_exception("tournament " . $tournament->get_id() . " doesn't belong to given game");
        }
    }

    /**
     * Checks that the given $state is a valid tournament state.
     *
     * @param string $state
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_tournament_state($state) {
        if (!\in_array($state, tournament::STATES)) {
            throw new invalid_parameter_exception("tournament state $state is not allowed.");
        }
    }

    /**
     * Gets the game instance from the database.
     *
     * @param cm_info $coursemodule
     *
     * @return game
     * @throws dml_exception
     */
    public static function get_game(cm_info $coursemodule): game {
        $game = new game();
        $game->load_data_by_id($coursemodule->instance);
        return $game;
    }

    /**
     * Gets the tournament instance for the given $tournamentid from the database.
     *
     * @param int $tournamentid
     *
     * @return tournament
     * @throws dml_exception
     */
    public static function get_tournament($tournamentid): tournament {
        $tournament = new tournament();
        $tournament->load_data_by_id($tournamentid);
        return $tournament;
    }

    /**
     * Gets the gamesession instance for the given $gamesessionid from the database.
     *
     * @param int $gamesessionid
     *
     * @return tournament_gamesession
     * @throws dml_exception
     */
    public static function get_gamesession($gamesessionid): tournament_gamesession {
        $gamesession = new tournament_gamesession();
        $gamesession->load_data_by_id($gamesessionid);
        return $gamesession;
    }

    /**
     * Loads a level by its id.
     *
     * @param int $levelid
     *
     * @return level
     * @throws dml_exception
     */
    public static function get_level($levelid): level {
        $level = new level();
        $level->load_data_by_id($levelid);
        return $level;
    }

    /**
     * Loads a question by its id.
     *
     * @param int $questionid
     *
     * @return tournament_question
     * @throws dml_exception
     */
    public static function get_question($questionid): tournament_question {
        $question = new tournament_question();
        $question->load_data_by_id($questionid);
        return $question;
    }

    /**
     * Gets or creates a gamesession for the current user. Allowed existing gamesessions are either in state
     * PROGRESS or FINISHED.
     *
     * @param game $game
     *
     * @return tournament_gamesession
     * @throws dml_exception
     */
    public static function get_or_create_gamesession(game $game): tournament_gamesession {
        global $DB, $USER;
        // try to find existing in-progress or finished gamesession
        $sql = "
            SELECT *
              FROM {challenge_gamesessions}
             WHERE game = :game AND mdl_user = :mdl_user AND state IN (:state_progress, :state_finished)
          ORDER BY timemodified DESC
        ";
        $params = [
            'game' => $game->get_id(),
            'mdl_user' => $USER->id,
            'state_progress' => tournament_gamesession::STATE_PROGRESS,
            'state_finished' => tournament_gamesession::STATE_FINISHED,
        ];
        $record = $DB->get_record_sql($sql, $params);
        // get or create game session
        if ($record === false) {
            $gamesession = self::insert_gamesession($game);
        } else {
            $gamesession = new tournament_gamesession();
            $gamesession->apply($record);
        }
        return $gamesession;
    }

    /**
     * Closes all game sessions of the current user, which are in state 'progress'.
     *
     * @param game $game
     *
     * @return void
     * @throws dml_exception
     */
    public static function dump_running_gamesessions(game $game) {
        global $DB, $USER;
        $conditions = [
            'game' => $game->get_id(),
            'mdl_user' => $USER->id,
            'state' => tournament_gamesession::STATE_PROGRESS,
        ];
        $gamesession = new tournament_gamesession();
        $DB->set_field($gamesession->get_table_name(), 'state', $gamesession::STATE_DUMPED, $conditions);
    }

    /**
     * Inserts a new game session into the DB (for the current user).
     *
     * @param game $game
     *
     * @return tournament_gamesession
     * @throws dml_exception
     */
    public static function insert_gamesession(game $game): tournament_gamesession {
        global $USER;
        $gamesession = new tournament_gamesession();
        $gamesession->set_game($game->get_id());
        $gamesession->set_mdl_user($USER->id);
        $level_ids = \array_map(function (level $level) {
            return $level->get_id();
        }, $game->get_active_levels());
        if ($game->is_shuffle_levels()) {
            \shuffle($level_ids);
        }
        $gamesession->set_levels_order(implode(',', $level_ids));
        $gamesession->save();
        return $gamesession;
    }
}
