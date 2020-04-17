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

/**
 * This file defines available ajax calls for mod_challenge.
 *
 * @package    mod_challenge
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    // ADMIN FUNCTIONS
    'mod_challenge_admin_delete_round' => [
        'classname' => 'mod_challenge\external\admin_delete_round',
        'methodname' => 'request',
        'description' => 'Deletes the given round from the game.',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_admin_get_categories' => [
        'classname' => 'mod_challenge\external\admin_get_categories',
        'methodname' => 'request',
        'description' => 'Get categories of the game.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_admin_get_mdl_categories' => [
        'classname' => 'mod_challenge\external\admin_get_mdl_categories',
        'methodname' => 'request',
        'description' => 'Retrieves the moodle question categories which are applicable for this game.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_admin_save_round' => [
        'classname' => 'mod_challenge\external\admin_save_round',
        'methodname' => 'request',
        'description' => 'Saves the given round.',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_admin_start_round' => [
        'classname' => 'mod_challenge\external\admin_start_round',
        'methodname' => 'request',
        'description' => 'Starts the given round.',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_admin_stop_round' => [
        'classname' => 'mod_challenge\external\admin_stop_round',
        'methodname' => 'request',
        'description' => 'Stops the given round.',
        'type' => 'write',
        'ajax' => true,
    ],
    // GENERIC (ADMIN AND PLAYER) FUNCTIONS
    'mod_challenge_main_get_game' => [
        'classname' => 'mod_challenge\external\main_get_game',
        'methodname' => 'request',
        'description' => 'Get options of the game.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_main_get_rounds' => [
        'classname' => 'mod_challenge\external\main_get_rounds',
        'methodname' => 'request',
        'description' => 'Get rounds of the active game.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_main_get_mdl_users' => [
        'classname' => 'mod_challenge\external\main_get_mdl_users',
        'methodname' => 'request',
        'description' => 'Get moodle users which have access to the given game',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_main_get_mdl_question' => [
        'classname' => 'mod_challenge\external\main_get_mdl_question',
        'methodname' => 'request',
        'description' => 'Retrieves the data of the given moodle question.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_main_get_mdl_answers' => [
        'classname' => 'mod_challenge\external\main_get_mdl_answers',
        'methodname' => 'request',
        'description' => 'Retrieves the moodle answers for a given moodle question.',
        'type' => 'read',
        'ajax' => true,
    ],
    // PLAYER FUNCTIONS
    'mod_challenge_player_get_question' => [
        'classname' => 'mod_challenge\external\player_get_question',
        'methodname' => 'request',
        'description' => 'Retrieves a new or existing question entity for the current match.',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_player_get_questions' => [
        'classname' => 'mod_challenge\external\player_get_questions',
        'methodname' => 'request',
        'description' => 'Retrieves all questions of a game for the current user.',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_player_save_answer' => [
        'classname' => 'mod_challenge\external\player_save_answer',
        'methodname' => 'request',
        'description' => 'Saves the given answer for the current question.',
        'type' => 'write',
        'ajax' => true,
    ],
];
