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

defined('MOODLE_INTERNAL') || die();

// capabilities
define('MOD_CHALLENGE_CAP_ADD_INSTANCE', 'mod/challenge:addinstance');
define('MOD_CHALLENGE_CAP_MANAGE', 'mod/challenge:manage');
define('MOD_CHALLENGE_CAP_VIEW', 'mod/challenge:view');

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
        case FEATURE_MOD_INTRO:
        case FEATURE_USES_QUESTIONS:
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

    // rounds, matches, questions and attempts
    $round_ids = $DB->get_fieldset_select('challenge_rounds', 'id', 'game = :game', ['game' => $challenge->id]);
    if ($round_ids) {
        foreach ($round_ids as $round_id) {
            $match_ids = $DB->get_fieldset_select('challenge_matches', 'id', 'round = :round', ['round' => $round_id]);
            if ($match_ids) {
                foreach ($match_ids as $match_id) {
                    $question_ids = $DB->get_fieldset_select('challenge_questions', 'id', 'matchid = :matchid', ['matchid' => $match_id]);
                    if ($question_ids) {
                        $result &= $DB->delete_records_list('challenge_attempts', 'question', $question_ids);
                        $result &= $DB->delete_records_list('challenge_questions', 'id', $question_ids);
                    }
                }
                $result &= $DB->delete_records('challenge_matches', ['round' => $round_id]);
            }
        }
        $result &= $DB->delete_records('challenge_rounds', ['game' => $challenge->id]);
    }

    // categories
    $result &= $DB->delete_records('challenge_categories', ['game' => $challenge->id]);

    // challenge itself
    $result &= $DB->delete_records('challenge', ['id' => $challenge->id]);
    return $result;
}
