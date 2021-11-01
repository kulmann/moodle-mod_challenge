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
 * This file keeps track of upgrades to mod_challenge.
 *
 * @package    mod_challenge
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_challenge\util;

defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for mod_challenge.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_challenge_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2021041800) {
        $table = new xmldb_table('challenge_matches');
        $field1 = new xmldb_field('mdl_user_1_completed', XMLDB_TYPE_INTEGER, '1', true, null, null, '0');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('mdl_user_2_completed', XMLDB_TYPE_INTEGER, '1', true, null, null, '0');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_mod_savepoint(true, 2021041800, 'challenge');
    }

    if ($oldversion < 2021053001) {
        $table = new xmldb_table('challenge_matches');
        $field1 = new xmldb_field('completed', XMLDB_TYPE_INTEGER, '1', true, null, null, '0');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        upgrade_mod_savepoint(true, 2021053001, 'challenge');
    }

    if ($oldversion < 2021053002) {
        // (re-)calculate the "completed", "mdl_user_1_completed" and "mdl_user_2_completed" columns
        $matchids = $DB->get_fieldset_sql("SELECT id FROM {challenge_matches}");
        foreach($matchids as $matchid) {
            $match = util::get_match($matchid);
            $game = util::get_game_by_roundid($match->get_round());
            if (!$match->is_completed()) {
                // up to this point in time, matches were set to "completed" for both users when time ran out.
                $match->set_completed($match->is_mdl_user_1_completed() && $match->is_mdl_user_2_completed());
            }
            $match->set_mdl_user_1_completed($match->is_mdl_user_1_finished($game->get_question_count()));
            $match->set_mdl_user_2_completed($match->is_mdl_user_2_finished($game->get_question_count()));
            $match->save();
        }
        upgrade_mod_savepoint(true, 2021053002, 'challenge');
    }

    if ($oldversion < 2021061100) {
        $table = new xmldb_table('challenge_users');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', true, null, true, null);
        $table->add_field('mdl_user', XMLDB_TYPE_INTEGER, '10', true, null, null, null);
        $table->add_field('game', XMLDB_TYPE_INTEGER, '10', true, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $dbman->create_table($table);
        upgrade_mod_savepoint(true, 2021061100, 'challenge');
    }

    if ($oldversion < 2021072400) {
        $table = new xmldb_table('challenge_messages');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', true, null, true, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', true, null, null, null);
        $table->add_field('game', XMLDB_TYPE_INTEGER, '10', true, null, null, null);
        $table->add_field('round', XMLDB_TYPE_INTEGER, '10', true, null, null, null);
        $table->add_field('matchid', XMLDB_TYPE_INTEGER, '10', true, null, null, null);
        $table->add_field('mdl_user', XMLDB_TYPE_INTEGER, '10', true, null, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '50', null, null, null, 'pending');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $dbman->create_table($table);
        upgrade_mod_savepoint(true, 2021072400, 'challenge');
    }

    if ($oldversion < 2021110100) {
        $table = new xmldb_table('challenge_rounds');
        $field = new xmldb_field('matches', XMLDB_TYPE_INTEGER, '5', true, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021110100, 'challenge');
    }

    if ($oldversion < 2021110101) {
        $table = new xmldb_table('challenge');
        $field = new xmldb_field('round_matches', XMLDB_TYPE_INTEGER, '5', true, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021110101, 'challenge');
    }

    return true;
}
