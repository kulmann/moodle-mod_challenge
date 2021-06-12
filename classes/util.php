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
use coding_exception;
use context;
use dml_exception;
use invalid_parameter_exception;
use mod_challenge\model\attempt;
use mod_challenge\model\category;
use mod_challenge\model\game;
use mod_challenge\model\match;
use mod_challenge\model\participant;
use mod_challenge\model\question;
use mod_challenge\model\round;
use required_capability_exception;

class util {

    /**
     * Checks if the logged in user has the given $capability.
     *
     * @param string $capability
     * @param context $context
     * @param int|null $userid
     *
     * @return bool
     * @throws coding_exception
     */
    public static function user_has_capability(string $capability, context $context, $userid = null): bool {
        return \has_capability($capability, $context, $userid);
    }

    /**
     * Kills the current request if the logged in user doesn't have the required capabilities.
     *
     * @param string $capability
     * @param context $context
     * @param int|null $userid
     *
     * @return void
     * @throws required_capability_exception
     */
    public static function require_user_has_capability(string $capability, context $context, $userid = null) {
        \require_capability($capability, $context, $userid);
    }

    /**
     * Checks that the round belongs to the given $game.
     *
     * @param game $game
     * @param round $round
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_round(game $game, round $round) {
        if ($game->get_id() !== $round->get_game()) {
            throw new invalid_parameter_exception("round " . $round->get_id() . " doesn't belong to given game");
        }
    }

    /**
     * Checks that the round is running.
     *
     * @param round $round
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_round_running(round $round) {
        if (!$round->is_started() || $round->is_ended()) {
            throw new invalid_parameter_exception("round " . $round->get_id() . " is not running");
        }
    }

    /**
     * Checks that the round is pending.
     *
     * @param round $round
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_round_pending(round $round) {
        if ($round->is_started()) {
            throw new invalid_parameter_exception("round " . $round->get_id() . " is not pending");
        }
    }

    /**
     * Checks that the match belongs to the given $round.
     *
     * @param game $game
     * @param match $match
     *
     * @throws invalid_parameter_exception
     */
    public static function validate_match(game $game, match $match) {
        try {
            $round = self::get_round($match->get_round());
        } catch (dml_exception $e) {
            throw new invalid_parameter_exception("match " . $match->get_id() . " doesn't belong to given game");
        }
        self::validate_round($game, $round);
    }

    /**
     * Checks that the category belongs to the given $game.
     *
     * @param game $game
     * @param category $category
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_category(game $game, category $category) {
        if ($game->get_id() !== $category->get_game()) {
            throw new invalid_parameter_exception("category " . $category->get_id() . " doesn't belong to given game");
        }
    }

    /**
     * Gets the game instance for the given $coursemodule from the database.
     *
     * @param cm_info $coursemodule
     *
     * @return game
     * @throws dml_exception
     */
    public static function get_game(cm_info $coursemodule): game {
        return self::get_game_by_id($coursemodule->instance);
    }

    /**
     * Gets the game instance for the given $gameid from the database.
     *
     * @param int $gameid
     *
     * @return game
     * @throws dml_exception
     */
    public static function get_game_by_id($gameid): game {
        $game = new game();
        $game->load_data_by_id($gameid);
        return $game;
    }

    /**
     * Gets a game instance by the given $roundid from the database.
     *
     * @param int $roundid
     *
     * @return game
     * @throws dml_exception
     */
    public static function get_game_by_roundid($roundid): game {
        $round = self::get_round($roundid);
        return self::get_game_by_id($round->get_game());
    }

    /**
     * Gets the round instance for the given $roundid from the database.
     *
     * @param int $roundid
     *
     * @return round
     * @throws dml_exception
     */
    public static function get_round($roundid): round {
        $round = new round();
        $round->load_data_by_id($roundid);
        return $round;
    }

    /**
     * Gets the category instance for the given $categoryid from the database.
     *
     * @param int $categoryid
     *
     * @return category
     * @throws dml_exception
     */
    public static function get_category($categoryid): category {
        $category = new category();
        $category->load_data_by_id($categoryid);
        return $category;
    }

    /**
     * Gets the match instance for the given $matchid from the database.
     *
     * @param int $matchid
     *
     * @return match
     * @throws dml_exception
     */
    public static function get_match($matchid): match {
        $match = new match();
        $match->load_data_by_id($matchid);
        return $match;
    }

    /**
     * Loads a question by its id.
     *
     * @param int $questionid
     *
     * @return question
     * @throws dml_exception
     */
    public static function get_question($questionid): question {
        $question = new question();
        $question->load_data_by_id($questionid);
        return $question;
    }

    /**
     * Creates a user object from the given std object and augments it with
     * metadata from the database if present.
     *
     * @param \stdClass $mdl_user
     *
     * @return participant
     */
    public static function get_user(\stdClass $mdl_user): participant {
        $user = new participant($mdl_user);
        try {
            $user->load_data_by_id($user->get_id());
        } catch (dml_exception $ignored) {
            // the metadata is optional. fail silently if not found and rely on the class defaults.
        }
        return $user;
    }

    /**
     * Tries to load an attempt by the given moodle user and question.
     *
     * @param int $questionid
     * @param int $mdl_user_id
     * @return attempt|null
     * @throws dml_exception
     */
    public static function get_attempt_by_question($questionid, $mdl_user_id) {
        global $DB;
        $record = $DB->get_record('challenge_attempts', ['question' => $questionid, 'mdl_user' => $mdl_user_id]);
        if ($record) {
            $attempt = new attempt();
            $attempt->load_data_by_id($record->id);
            return $attempt;
        }
        return null;
    }
}
