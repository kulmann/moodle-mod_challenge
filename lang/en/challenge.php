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
$string['question_count'] = 'Questions per Match';
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
$string['round_matches'] = 'Matches per participant';
$string['round_matches_help'] = 'Number of matches per participant which are generated evenly throughout the runtime of one round (see duration config above).';
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

/* messaging */
$string['messageprovider:match'] = 'Invitation to a new quiz challenge match';
$string['message_match_invitation_subject'] = 'Quiz Challenge invitation';
$string['message_match_invitation_message_html'] = 'Hi {$a->fullname},<br />

you have been invited to round {$a->roundnumber} of the Quiz Challenge »{$a->gamename}« in your Moodle course »{$a->coursename}«.<br />

<a href="{$a->matchurl}">Click here</a> to see your questions for this round.<br />

Good luck!';
$string['message_match_invitation_message_plain'] = 'Hi {$a->fullname},

you have been invited to round {$a->roundnumber} of the Quiz Challenge »{$a->gamename}« in your Moodle course »{$a->coursename}«.

Follow the link to see your questions for this round:
{$a->matchurl}

Good luck!';
$string['task_validate_rounds'] = 'Process scheduled Quiz Challenge game rounds';
$string['task_send_match_invitations'] = 'Send out Quiz Challenge game round invitations';

/* loading screen in vue app */
$string['loading_screen_title'] = 'Loading Quiz Challenge...';

/* formatting */
$string['format_date_time'] = 'DD.MM.YYYY, hh:mm a';
$string['format_date'] = 'DD.MM.YYYY';
$string['format_time'] = 'hh:mm a';
$string['format_time_suffix'] = '';// don't add a suffix, since the time already has am or pm.

/* admin screen in vue app */
$string['admin_screen_title'] = 'Edit game content';
$string['admin_not_allowed'] = 'You have insufficient permissions to view this page.';
$string['admin_btn_save'] = 'Save';
$string['admin_btn_cancel'] = 'Cancel';
$string['admin_btn_add'] = 'Add';
$string['admin_btn_confirm_publish'] = 'Confirm Publishing';
$string['admin_btn_confirm_delete'] = 'Confirm Delete';
$string['admin_btn_confirm_stop'] = 'Confirm Stop';
$string['admin_btn_generate'] = 'Generate';
$string['admin_btn_datepicker_cancel'] = "Cancel";
$string['admin_btn_datepicker_submit'] = "Select";
$string['admin_avatar_alt'] = 'Picture of {$a}';
$string['admin_nav_rounds'] = 'Rounds';
$string['admin_nav_users'] = 'Participants';
$string['admin_nav_highscore'] = 'Leader Board';

/* admin: rounds */
$string['admin_rounds_title'] = 'Manage rounds';
$string['admin_rounds_intro'] = 'A Quiz-Challenge consists of rounds. For each round, random pairs of course participants are invited to answer questions. You can select the timeframe for participation for each round. Not answering questions within that timeframe results in not gaining points. You can create additional rounds at any time. They are allowed to overlap in time.';
$string['admin_rounds_empty'] = 'You have not created any rounds, yet. Click on "Add" to create one.';
$string['admin_rounds_list_th_no'] = 'No.';
$string['admin_rounds_list_th_name'] = 'Name';
$string['admin_rounds_list_th_timing'] = 'Timing';
$string['admin_rounds_list_th_actions'] = 'Actions';
$string['admin_rounds_list_timing_open'] = 'open';
$string['admin_rounds_list_timing_from'] = 'From {$a}';
$string['admin_rounds_list_timing_to'] = 'Until {$a}';
$string['admin_round_delete_confirm'] = 'Do you really want to delete round {$a}?';
$string['admin_round_stop_confirm'] = 'Do you really want to stop round {$a} ahead of it\'s regular expiration? Warning: Matches with unanswered questions are lost.';
$string['admin_round_datepicker_start'] = "Select round start";
$string['admin_round_edit_title_edit'] = 'Edit round {$a}';
$string['admin_round_edit_title_add'] = 'Create round {$a}';
$string['admin_round_edit_description'] = 'Add new question categories so that they are included in this round and all following rounds. You can remove question categories as well - they will be excluded for this and all following rounds, but will remain in previous rounds. Please be aware that already saved categories can not be changed anymore, but only be removed.';
$string['admin_round_lbl_name'] = 'Name';
$string['admin_round_categories_title'] = 'Edit question categories';
$string['admin_round_lbl_category_open'] = 'Category {$a->number} - Since round {$a->round_first_number}';
$string['admin_round_lbl_category_closed_range'] = 'Category {$a->number} - From round {$a->round_first_number} to {$a->round_last_number}';
$string['admin_round_lbl_category_closed_same'] = 'Category {$a->number} - In round {$a->round_first_number} only';
$string['admin_round_lbl_category_new'] = 'Category {$a} - added just now';
$string['admin_round_lbl_category_please_select'] = 'Select category';
$string['admin_round_msg_saving'] = 'Saving the round, please wait';

/* admin: users */
$string['admin_users_participants_title'] = 'Manage Participants';
$string['admin_users_participants_intro'] = 'Keep track of which course participants are actively involved in this Quiz-Challenge.';
$string['admin_users_participants_thead_user'] = 'Name';
$string['admin_users_participants_thead_status'] = 'Status';
$string['admin_users_participants_thead_round'] = 'Round {$a}';
$string['admin_users_participants_status_enabled'] = 'Active';
$string['admin_users_participants_status_disabled'] = 'Banned';
$string['admin_users_teachers_title'] = 'Teachers';
$string['admin_users_teachers_intro'] = 'The following users have edit permissions for this course. They don\'t qualify as participants.';

/* admin: round results */
$string['admin_results_loading'] = 'Loading round results';
$string['admin_results_title'] = 'Results of round {$a}';
$string['admin_results_btn_rounds'] = 'Go Back';
$string['admin_results_pending'] = 'Pairings and results will be available after the round was started.';
$string['admin_results_match_group'] = 'Matches {$a->number} (created on {$a->date} at {$a->time})';
$string['admin_results_no_matches'] = 'There are no matches for this round, yet, because there are no active participants (see participant management).';
$string['admin_results_match_score_table_question'] = 'Question {$a}';
$string['admin_results_match_score_state_finished'] = 'Finished';
$string['admin_results_match_score_state_ongoing'] = 'Ongoing';

/* game gui */
$string['game_screen_title'] = 'Play »Quiz Challenge«';
$string['game_nav_play'] = 'Play';
$string['game_nav_highscore'] = 'Leader Board';
$string['game_not_allowed'] = 'You are not allowed to participate in a »Quiz Challenge«.';
$string['game_not_started'] = 'This »Quiz Challenge« has not been started, yet. Please wait until the first round started.';
$string['game_qtype_not_supported'] = 'The question type »{$a}« is not supported.';
$string['game_loading_question'] = 'Loading question details';
$string['game_btn_continue'] = 'Continue';
$string['game_btn_show'] = 'Show';
$string['game_btn_answer'] = 'Answer';
$string['game_match_step'] = 'Round {$a->step} / {$a->total}';
$string['game_match_loading'] = 'Loading match data';
$string['game_match_show_error'] = 'Something went wrong trying to show the selected match.';
$string['game_match_versus'] = 'vs.';
$string['game_match_step'] = 'Round {$a->step} / {$a->total}';
$string['game_match_lbl_open'] = 'open';
$string['game_match_lbl_won'] = 'won';
$string['game_match_lbl_lost'] = 'lost';
$string['game_match_lbl_question'] = 'Answer Question {$a}';
$string['game_match_question_title'] = 'Question {$a}';
$string['game_round_dates'] = 'Active from <b>{$a->start}</b> until <b>{$a->end}</b>';

/* shared screens */
$string['shared_highscore_title'] = 'Leader Board';
$string['shared_highscore_loading'] = 'Loading leader board';
$string['shared_highscore_loading_failed'] = 'An error occurred while loading the leader board.';
$string['shared_highscore_empty'] = 'No one is on the leader board, yet.';
$string['shared_highscore_rank'] = 'Rank';
$string['shared_highscore_user'] = 'User';
$string['shared_highscore_score_total'] = 'Score';
$string['shared_highscore_score_best'] = 'Best Round';
