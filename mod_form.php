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
 * The main challenge configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_challenge
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/lib/questionlib.php');

/**
 * Module instance settings form
 */
class mod_challenge_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('challengename', 'challenge'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'challengename', 'challenge');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements(get_string('introduction', 'challenge'));

        // Game options
        $mform->addElement('header', 'game_options_fieldset', get_string('game_options_fieldset', 'challenge'));
        // ... question count per gamesession
        $mform->addElement('text', 'question_count', get_string('question_count', 'challenge'), ['size' => 5]);
        $mform->setType('question_count', PARAM_INT);
        $mform->setDefault('question_count', 3);
        $mform->addHelpButton('question_count', 'question_count', 'challenge');
        // ... question duration
        $mform->addElement('text', 'question_duration', get_string('question_duration', 'challenge'), ['size' => 5]);
        $mform->setType('question_duration', PARAM_INT);
        $mform->setDefault('question_duration', 30);
        $mform->addHelpButton('question_duration', 'question_duration', 'challenge');
        // ... review duration
        $mform->addElement('text', 'review_duration', get_string('review_duration', 'challenge'), ['size' => 5]);
        $mform->setType('review_duration', PARAM_INT);
        $mform->setDefault('review_duration', 2);
        $mform->addHelpButton('review_duration', 'review_duration', 'challenge');
        // ... shuffle answers?
        $mform->addElement('advcheckbox', 'question_shuffle_answers', get_string('question_shuffle_answers', 'challenge'), '&nbsp;');
        $mform->setDefault('question_shuffle_answers', 1);
        $mform->addHelpButton('question_shuffle_answers', 'question_shuffle_answers', 'challenge');

        // ... tile height for level cards
        $level_tile_heights = [];
        foreach (MOD_CHALLENGE_LEVEL_TILE_HEIGHTS as $height) {
            $level_tile_heights[$height] = get_string('level_tile_height_' . $height, 'challenge');
        }
        $mform->addElement('select', 'level_tile_height', get_string('level_tile_height', 'challenge'), $level_tile_heights);
        $mform->setDefault('level_tile_height', MOD_CHALLENGE_LEVEL_TILE_HEIGHT_MEDIUM);
        $mform->addHelpButton('level_tile_height', 'level_tile_height', 'challenge');
        // ... tile overlay alpha
        $level_tile_alphas = [];
        for($i=0; $i<=10; $i++) {
            $level_tile_alphas[$i * 10] = ($i * 10) . "%";
        }
        $mform->addElement('select', 'level_tile_alpha', get_string('level_tile_alpha', 'challenge'), $level_tile_alphas);
        $mform->setDefault('level_tile_alpha', 50);
        $mform->addHelpButton('level_tile_alpha', 'level_tile_alpha', 'challenge');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
