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
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'mod_challenge_get_levels' => [
        'classname' => 'mod_challenge\external\levels',
        'methodname' => 'get_levels',
        'description' => 'Get levels for the game overview.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_get_level_categories' => [
        'classname' => 'mod_challenge\external\levels',
        'methodname' => 'get_level_categories',
        'description' => 'Get categories for one specific level.',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_set_level_position' => [
        'classname' => 'mod_challenge\external\levels',
        'methodname' => 'set_level_position',
        'description' => 'Modify the level position by 1 (up or down).',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_delete_level' => [
        'classname' => 'mod_challenge\external\levels',
        'methodname' => 'delete_level',
        'description' => 'Delete a certain level',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_save_level' => [
        'classname' => 'mod_challenge\external\levels',
        'methodname' => 'save_level',
        'description' => 'Save a certain level and its categories',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_get_game' => [
        'classname' => 'mod_challenge\external\game',
        'methodname' => 'get_game',
        'description' => 'Get options of the game and logged in user',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_get_tournaments' => [
        'classname' => 'mod_challenge\external\tournaments',
        'methodname' => 'get_tournaments',
        'description' => 'Get tournaments of a game having a specified state.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_get_question' => [
        'classname' => 'mod_challenge\external\tournament_gamesessions',
        'methodname' => 'get_question',
        'description' => 'Gets or creates the current question from the current gamesession for the logged in user.',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_submit_answer' => [
        'classname' => 'mod_challenge\external\tournament_gamesessions',
        'methodname' => 'submit_answer',
        'description' => 'Submit answer for the current question',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_cancel_answer' => [
        'classname' => 'mod_challenge\external\tournament_gamesessions',
        'methodname' => 'cancel_answer',
        'description' => 'Submit that time ran out for the current question',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_challenge_get_mdl_question' => [
        'classname' => 'mod_challenge\external\questionbank',
        'methodname' => 'get_mdl_question',
        'description'  => 'Retrieves the data of the given moodle question.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_get_mdl_answers' => [
        'classname' => 'mod_challenge\external\questionbank',
        'methodname' => 'get_mdl_answers',
        'description'  => 'Retrieves the moodle answers for a given moodle question.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_challenge_get_mdl_categories' => [
        'classname' => 'mod_challenge\external\questionbank',
        'methodname' => 'get_mdl_categories',
        'description'  => 'Retrieves the moodle question categories which are applicable for this game.',
        'type' => 'read',
        'ajax' => true,
    ],
];
