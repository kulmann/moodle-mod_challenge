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
 * English strings for challenge
 *
 * @package    mod_challenge
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* system */
$string['modulename'] = 'Quiz Challenge';
$string['modulenameplural'] = 'Quiz Challenges';
$string['modulename_help'] = 'This is a quiz game with customizable question categories. Faster answers on questions result in higher scores. Course participants get ranked in a leader board by their high scores.';
$string['pluginadministration'] = '»Quiz Challenge« Administration';
$string['pluginname'] = 'Quiz Challenge';
$string['challenge'] = 'Quiz Challenge';
$string['challenge:addinstance'] = 'Add a new »Quiz Challenge«';
$string['challenge:submit'] = 'Submit »Quiz Challenge«';
$string['challenge:manage'] = 'Manage »Quiz Challenge«';
$string['challenge:view'] = 'View »Quiz Challenge«';
$string['challengename'] = 'Name';
$string['challengename_help'] = 'Please provide a name for this »Quiz Challenge«.';
$string['introduction'] = 'Description';
$string['route_not_found'] = 'The page you tried to open doesn\'t exist.';

/* main admin form: game options */
$string['game_options_fieldset'] = 'Game options';
$string['question_count'] = 'Questions per Round';
$string['question_count_help'] = 'Number of questions, each opponent has to answer per round.';
$string['question_duration'] = 'Question duration (seconds)';
$string['question_duration_help'] = 'Available time (in seconds) for each question.';
$string['review_duration'] = 'Time to display solution (seconds)';
$string['review_duration_help'] = 'The duration how long the solution of a question is being displayed (in seconds) before continuing to the next question automatically.';
$string['question_shuffle_answers'] = 'Shuffle answers';
$string['question_shuffle_answers_help'] = 'If enabled, the answers of questions will be shuffled.';

/* main admin form: round options */
$string['rounds_fieldset'] = 'Rounds';
$string['round_duration_unit'] = 'Duration unit';
$string['round_duration_unit_help'] = 'Time unit for the duration of one round.';
$string['round_duration_unit_hours'] = 'Hours';
$string['round_duration_unit_days'] = 'Days';
$string['round_duration_unit_weeks'] = 'Weeks';
$string['round_duration_value'] = 'Duration value';
$string['round_duration_value_help'] = 'Amount for the selected unit to determine the duration of one round.';
$string['rounds'] = 'Number of rounds';
$string['rounds_help'] = 'Total number of rounds until final score. When setting this to 0 the game will run forever / until end of course.';

/* activity edit page: control */
$string['control_edit'] = 'Control';
$string['control_edit_title'] = 'Control Options';
$string['reset_progress_heading'] = 'Reset Progress';
$string['reset_progress_button'] = 'Reset Progress';
$string['reset_progress_confirm_title'] = 'Confirm Reset Progress';
$string['reset_progress_confirm_question'] = 'Are you sure you want to reset the progress? this will delete all the results and is irreversible';

/* course reset */
$string['course_reset_include_progress'] = 'Reset progress (Highscores etc.)';
$string['course_reset_include_topics'] = 'Reset topics etc. (Everything will be deleted!)';

/* loading screen in vue app */
$string['loading_screen_title'] = 'Loading Quiz Challenge...';

/* formatting */
$string['format_date_time'] = 'DD.MM.YYYY, hh:mm';
$string['format_date'] = 'DD.MM.YYYY';
$string['format_time'] = 'hh:mm';

/* admin screen in vue app */
$string['admin_screen_title'] = 'Edit game content';
$string['admin_not_allowed'] = 'You have insufficient permissions to view this page.';
$string['admin_btn_save'] = 'Save';
$string['admin_btn_cancel'] = 'Cancel';
$string['admin_btn_add'] = 'Add';
$string['admin_btn_confirm_publish'] = 'Confirm Publishing';
$string['admin_btn_confirm_delete'] = 'Confirm Delete';
$string['admin_btn_confirm_start'] = 'Confirm Start';
$string['admin_btn_confirm_stop'] = 'Confirm Stop';
$string['admin_btn_generate'] = 'Generate';
$string['admin_avatar_alt'] = 'Picture of {$a}';
/* admin: rounds */
$string['admin_nav_rounds'] = 'Rounds';
$string['admin_rounds_title'] = 'Manager rounds';
$string['admin_rounds_intro'] = 'Description text about rounds...';
$string['admin_rounds_list_th_no'] = 'No.';
$string['admin_rounds_list_th_name'] = 'Name';
$string['admin_rounds_list_th_timing'] = 'Timing';
$string['admin_rounds_list_th_actions'] = 'Actions';
$string['admin_rounds_list_timing_open'] = 'open';
$string['admin_rounds_list_timing_from'] = 'From {$a}';
$string['admin_rounds_list_timing_to'] = 'Until {$a}';
$string['admin_round_delete_confirm'] = 'Do you really want to delete round {$a}?';
$string['admin_round_start_confirm'] = 'Do you really want to start round {$a}?';
$string['admin_round_stop_confirm'] = 'Do you really want to stop round {$a} ahead of it\'s regular expiration?';
$string['admin_round_edit_title_edit'] = 'Edit round {$a}';
$string['admin_round_edit_title_add'] = 'Create round {$a}';
$string['admin_round_edit_title'] = 'Edit round {$a}';
$string['admin_round_edit_description'] = 'Add new question categories so that they are included in this round and all following rounds. You can remove question categories as well - they will be excluded for this and all following rounds, but will remain in previous rounds. Please be aware that already saved categories can not be changed anymore, but only be removed.';
$string['admin_round_lbl_name'] = 'Name';
$string['admin_round_categories_title'] = 'Edit question categories';
$string['admin_round_lbl_category_open'] = 'Category {$a->number} - Since round {$a->round_first_number}';
$string['admin_round_lbl_category_closed_range'] = 'Category {$a->number} - From round {$a->round_first_number} to {$a->round_last_number}';
$string['admin_round_lbl_category_closed_same'] = 'Category {$a->number} - In round {$a->round_first_number} only';
$string['admin_round_lbl_category_new'] = 'Category {$a} - Gerade hinzugefügt';
$string['admin_round_lbl_category_please_select'] = 'Select category';
$string['admin_round_msg_saving'] = 'Saving the round, please wait';


/* DEPRECATED */
/* admin: levels */
$string['admin_nav_levels'] = 'Topics';
$string['admin_levels_title'] = 'Edit topics';
$string['admin_levels_none'] = 'You didn\'t add any topics, yet.';
$string['admin_levels_intro'] = 'You have already created the following topics for this game. You may edit their data and order, or even delete them. Please note, that deleting topics only has effects for future tournaments.';
$string['admin_level_delete_confirm'] = 'Do you really want to delete the topic »{$a}«?';
$string['admin_level_title_add'] = 'Create topic {$a}';
$string['admin_level_title_edit'] = 'Edit topic {$a}';
$string['admin_level_loading'] = 'Loading topic data';
$string['admin_level_lbl_name'] = 'Name';
$string['admin_level_lbl_bgcolor'] = 'Background Color';
$string['admin_level_lbl_bgcolor_help'] = 'HEX format, with or without #, as 3 or 6 chars. Example: #cc0033 or #c03';
$string['admin_level_lbl_image'] = 'Background Image';
$string['admin_level_lbl_image_drag'] = 'Upload via drag&drop or select';
$string['admin_level_lbl_image_change'] = 'Change';
$string['admin_level_lbl_image_remove'] = 'Remove';
$string['admin_level_lbl_categories'] = 'Question assignments';
$string['admin_level_lbl_category'] = 'Category {$a}';
$string['admin_level_lbl_category_please_select'] = 'Select category';
$string['admin_level_msg_saving'] = 'Saving the topic, please wait';
/* admin: tournaments */
$string['admin_nav_tournaments'] = 'Tournaments';
$string['admin_tournaments_title'] = 'Edit tournaments';
$string['admin_tournaments_title_unpublished'] = 'Planned tournaments';
$string['admin_tournaments_none_unpublished'] = 'There are no planned tournaments.';
$string['admin_tournaments_intro_unpublished'] = 'Planned tournaments can be edited until they are published.';
$string['admin_tournaments_title_progress'] = 'Active tournaments';
$string['admin_tournaments_none_progress'] = 'There are no active tournaments.';
$string['admin_tournaments_intro_progress'] = 'Here you can observe active tournaments.';
$string['admin_tournaments_title_finished'] = 'Finished tournaments';
$string['admin_tournaments_none_finished'] = 'There are no finished tournaments.';
$string['admin_tournaments_intro_finished'] = 'Here you can evaluate finished tournaments.';
$string['admin_tournament_publish_confirm'] = 'Do you really want to publish the tournament »{$a}«? Participants will be invited to their first match immediately.';
$string['admin_tournament_delete_confirm'] = 'Do you really want to delete the tournament »{$a}«?';
$string['admin_tournament_title_add'] = 'Create tournament';
$string['admin_tournament_title_edit'] = 'Edit tournament';
$string['admin_tournament_loading'] = 'Loading tournament data';
$string['admin_tournament_lbl_name'] = 'Name';
$string['admin_tournament_title_matches'] = 'Edit tournament participants';
$string['admin_tournament_participants_loading'] = 'Loading tournament participants';
$string['admin_tournament_participants_saving'] = 'Saving the matches, please wait';
$string['admin_nav_matches_users'] = 'Select Participants';
$string['admin_nav_matches_pairs'] = 'Generate Matches';
$string['admin_tournament_match_invalid_users'] = 'Starting a tournament with the selected participants is impossible. Please be aware that an even number of participants is required.';
$string['admin_tournament_match_info_participants'] = '{$a} participants selected';
$string['admin_tournament_match_none_title'] = 'No matches';
$string['admin_tournament_match_none_msg'] = 'You didn\'t generate any participant matches so far. Please click on the »Generate« button.';
$string['admin_tournament_match_done_title'] = 'Matches generated';
$string['admin_tournament_match_done_msg'] = 'You have generated the following participant matches. If you agree with the current set, please click »Save«.';
$string['admin_tournament_match_table_number'] = 'Number';
$string['admin_tournament_match_table_participant'] = 'Participant';
$string['admin_tournament_title_topics'] = 'Edit tournament topics';
$string['admin_tournament_topics_loading'] = 'Loading tournament topics';
$string['admin_tournament_topics_saving'] = 'Saving the topics, please wait';
$string['admin_tournament_topics_lbl_step'] = 'Round';
$string['admin_tournament_topics_lbl_levels'] = 'Available topics';
$string['admin_tournament_topics_lbl_select'] = 'Please select a topic for round {$a}';
$string['admin_tournament_topics_lbl_none'] = 'None';





/* game gui */
$string['game_screen_title'] = 'Play »Quiz Challenge«';
$string['game_not_allowed'] = 'You are not allowed to participate in a »Quiz Challenge«.';
$string['game_qtype_not_supported'] = 'The question type »{$a}« is not supported.';
$string['game_loading_question'] = 'Loading question details';
$string['game_btn_continue'] = 'Continue';
$string['game_btn_show'] = 'Show';
$string['game_btn_answer'] = 'Answer';
$string['game_match_loading'] = 'Loading match data';
$string['game_match_show_error'] = 'Something went wrong trying to show the selected match.';
$string['game_match_versus'] = 'versus';
$string['game_match_step'] = 'Round {$a->step} / {$a->total}';
$string['game_match_lbl_open'] = 'open';
$string['game_match_lbl_won'] = 'won';
$string['game_match_lbl_lost'] = 'lost';
$string['game_match_lbl_question'] = 'Answer Question {$a}';
