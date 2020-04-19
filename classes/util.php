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
use mod_challenge\model\category;
use mod_challenge\model\match;
use mod_challenge\model\question;
use mod_challenge\model\game;
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
     * Checks that the question belongs to the given user.
     *
     * @param int $mdl_user_id
     * @param question $question
     *
     * @return void
     * @throws invalid_parameter_exception
     */
    public static function validate_question($mdl_user_id, question $question) {
        if ($mdl_user_id !== $question->get_mdl_user()) {
            throw new invalid_parameter_exception("question " . $question->get_id() . " doesn't belong to given moodle user $mdl_user_id ");
        }
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
        } catch(dml_exception $e) {
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
     * Checks if the question is already timed out and sets the question data accordingly.
     *
     * @param question $question
     * @param game $game
     * @throws dml_exception
     */
    public static function check_question_timeout(question $question, game $game) {
        if (!$question->is_finished() && $question->get_timecreated() + $game->get_question_duration() < \time()) {
            $question->set_mdl_answer_given(0);
            $question->set_finished(true);
            $question->set_correct(false);
            $question->set_score(0);
            $question->set_timeremaining(0);
            $question->save();
        }
    }

    /**
     * Checks if the given match is done, i.e. if it is not marked as finished, we determine (and persist) the winner, if there is one.
     *
     * @param match $match
     * @param game $game
     *
     * @throws dml_exception
     */
    public static function check_match_winner(match $match, game $game) {
        // load all questions related to this match and check if they timed out
        if (!$match->is_finished()) {
            $questions = $match->get_questions();
            // check if user 1 answered enough questions
            $user1 = $match->get_mdl_user_1();
            $questions_user1 = \array_filter($questions, function(question $question) use ($user1) {
                return $question->get_mdl_user() === $user1;
            });
            $answer_count_user1 = \count($questions_user1);
            if ($answer_count_user1 < $game->get_question_count()) {
                return;
            }
            // check if user 2 answered enough questions
            $user2 = $match->get_mdl_user_2();
            $questions_user2 = \array_filter($questions, function(question $question) use ($user2) {
                return $question->get_mdl_user() === $user2;
            });
            $answer_count_user2 = \count($questions_user2);
            if ($answer_count_user2 < $game->get_question_count()) {
                return;
            }
            // check which user won
            $win_count_user1 = \count(\array_filter($questions_user1, function(question $question) {
                return $question->is_correct();
            }));
            $win_count_user2 = \count(\array_filter($questions_user2, function(question $question) {
                return $question->is_correct();
            }));
            if ($win_count_user1 > $win_count_user2) {
                $match->set_mdl_user_winner($user1);
                $match->save();
                return;
            }
            if ($win_count_user2 > $win_count_user1) {
                $match->set_mdl_user_winner($user2);
                $match->save();
                return;
            }
            if ($win_count_user1 === $win_count_user2) {
                // tie breaking rule is the earlier participation
                $datetime_sum_user1 = \array_sum(\array_map(function(question $question) {
                    return $question->get_timecreated();
                }, $questions_user1));
                $datetime_sum_user2 = \array_sum(\array_map(function(question $question) {
                    return $question->get_timecreated();
                }, $questions_user2));
                $match->set_mdl_user_winner(($datetime_sum_user1 < $datetime_sum_user2) ? $user1 : $user2);
                $match->save();
                return;
            }
        }
    }
}
