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
 * Library of interface functions and constants for module challenge
 *
 * All the core Moodle functions, needed to integrate this plugin into Moodle at all,
 * should be placed here.
 *
 * All the challenge specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_challenge
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_challenge\model\tournament_gamesession;
use mod_challenge\model\level;

defined('MOODLE_INTERNAL') || die();

// implemented question types
define('MOD_CHALLENGE_QTYPE_SINGLE_CHOICE_DB', 'multichoice');
define('MOD_CHALLENGE_VALID_QTYPES_DB', [
    MOD_CHALLENGE_QTYPE_SINGLE_CHOICE_DB,
]);

// round duration units
define('MOD_CHALLENGE_ROUND_DURATION_UNIT_HOURS', 'hours');
define('MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS', 'days');
define('MOD_CHALLENGE_ROUND_DURATION_UNIT_WEEKS', 'weeks');
define('MOD_CHALLENGE_ROUND_DURATION_UNITS', [
    MOD_CHALLENGE_ROUND_DURATION_UNIT_HOURS,
    MOD_CHALLENGE_ROUND_DURATION_UNIT_DAYS,
    MOD_CHALLENGE_ROUND_DURATION_UNIT_WEEKS,
]);

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function challenge_supports($feature) {
    switch ($feature) {
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the challenge quiz into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $challenge Submitted data from the form in mod_form.php
 * @param mod_challenge_mod_form $mform The form instance itself (if needed)
 *
 * @return int The id of the newly inserted challenge record
 * @throws dml_exception
 */
function challenge_add_instance(stdClass $challenge, mod_challenge_mod_form $mform = null) {
    global $DB;
    // pre-processing
    $challenge->timecreated = time();
    challenge_preprocess_form_data($challenge);
    // insert into db
    $challenge->id = $DB->insert_record('challenge', $challenge);
    // some additional stuff
    challenge_after_add_or_update($challenge);
    return $challenge->id;
}

/**
 * Updates an instance of the challenge in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $challenge An object from the form in mod_form.php
 * @param mod_challenge_mod_form $mform The form instance itself (if needed)
 *
 * @return boolean Success/Fail
 * @throws dml_exception
 */
function challenge_update_instance(stdClass $challenge, mod_challenge_mod_form $mform = null) {
    global $DB;
    // pre-processing
    $challenge->id = $challenge->instance;
    challenge_preprocess_form_data($challenge);
    // update in db
    $result = $DB->update_record('challenge', $challenge);
    // some additional stuff
    challenge_after_add_or_update($challenge);
    return $result;
}


/**
 * Pre-process the challenge options form data, making any necessary adjustments.
 * Called by add/update instance in this file.
 *
 * @param stdClass $challenge The variables set on the form.
 */
function challenge_preprocess_form_data(stdClass $challenge) {
    // update timestamp
    $challenge->timemodified = time();
    // trim name.
    if (!empty($challenge->name)) {
        $challenge->name = trim($challenge->name);
    }
}

/**
 * This function is called at the end of challenge_add_instance
 * and challenge_update_instance, to do the common processing.
 *
 * @param stdClass $challenge the quiz object.
 */
function challenge_after_add_or_update(stdClass $challenge) {
    // nothing to do for now...
}

/**
 * Removes an instance of the challenge from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 *
 * @return boolean Success/Failure
 * @throws dml_exception
 */
function challenge_delete_instance($id) {
    global $DB;
    $result = true;
    $challenge = $DB->get_record('challenge', ['id' => $id], '*', MUST_EXIST);
    // game sessions, including chosen questions
    $gamesession_ids = $DB->get_fieldset_select('challenge_gamesessions', 'id', 'game = :game', ['game' => $challenge->id]);
    if ($gamesession_ids) {
        $result &= $DB->delete_records_list('challenge_questions', 'gamesession', $gamesession_ids);
    }
    $result &= $DB->delete_records('challenge_gamesessions', ['game' => $challenge->id]);
    // levels and categories
    $levels_ids = $DB->get_fieldset_select('challenge_levels', 'id', 'game = :game', ['game' => $challenge->id]);
    if ($levels_ids) {
        $result &= $DB->delete_records_list('challenge_categories', 'level', $levels_ids);
    }
    $result &= $DB->delete_records('challenge_levels', ['game' => $challenge->id]);
    $result &= $DB->delete_records('challenge', ['id' => $challenge->id]);
    return $result;
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

function challenge_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== level::FILE_AREA) {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('mod/challenge:view', $context)) {
        return false;
    }

    // Get the item id
    $itemid = array_shift($args); // The first item in the $args array.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_challenge', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
