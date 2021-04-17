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
 * German strings for challenge
 *
 * @package    mod_challenge
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* system */
$string['modulename'] = 'Quiz Challenge';
$string['modulenameplural'] = '»Quiz Challenge« Instanzen';
$string['modulename_help'] = 'Ein Quiz-Spiel, in dem Fragen aus Kategorien nach Schnelligkeit der Antwort bepunktet werden. Die Kursteilnehmer können mit Hilfe ihrer erreichten Punkte in eine Bestenliste sortiert werden.';
$string['pluginadministration'] = '»Quiz Challenge« Administration';
$string['pluginname'] = 'Quiz Challenge';
$string['challenge'] = 'Quiz Challenge';
$string['challenge:addinstance'] = '»Quiz Challenge« hinzufügen';
$string['challenge:submit'] = '»Quiz Challenge« speichern';
$string['challenge:manage'] = '»Quiz Challenge« verwalten';
$string['challenge:view'] = '»Quiz Challenge« anzeigen';
$string['challengename'] = 'Name';
$string['challengename_help'] = 'Bitte vergeben Sie einen Namen für dieses »Quiz Challenge«.';
$string['introduction'] = 'Beschreibung';
$string['route_not_found'] = 'Die aufgerufene Seite gibt es nicht.';

/* main admin form: game options */
$string['game_options_fieldset'] = 'Spieloptionen';
$string['question_count'] = 'Fragen pro Runde';
$string['question_count_help'] = 'Anzahl der Fragen, die jedem Opponenten einer Runde gestellt werden.';
$string['question_duration'] = 'Antwortzeit (Sekunden)';
$string['question_duration_help'] = 'Antwortzeit pro Frage (in Sekunden).';
$string['review_duration'] = 'Anzeigedauer Lösung (Sekunden)';
$string['review_duration_help'] = 'Anzeigezeit der Lösung (in Sekunden), bevor automatisch zur nächsten Frage weitergeleitet wird.';
$string['question_shuffle_answers'] = 'Antworten mischen';
$string['question_shuffle_answers_help'] = 'Wenn diese Option aktiviert ist, werden die Antworten der Fragen gemischt bevor sie angezeigt werden.';

/* main admin form: round options */
$string['rounds_fieldset'] = 'Spielrunden';
$string['round_duration_unit'] = 'Einheit der Dauer';
$string['round_duration_unit_help'] = 'Einheit für die Dauer einer Spielrunde bzw. für die Zeit nach welcher die nächste Spielrunde automatisch gestartet wird.';
$string['round_duration_unit_hours'] = 'Stunden';
$string['round_duration_unit_days'] = 'Tage';
$string['round_duration_unit_weeks'] = 'Wochen';
$string['round_duration_value'] = 'Dauer';
$string['round_duration_value_help'] = 'Wert für die Dauer einer Spielrunde bzw. für die Zeit nach welcher die nächste Spielrunde automatisch gestartet wird.';
$string['rounds'] = 'Anzahl Runden';
$string['rounds_help'] = 'Gesamtzahl Spielrunden bis zur Schlusswertung. Falls hier eine 0 eingetragen wird, werden unbegrenzt / bis zum Kursende weitere Spielrunden generiert.';

/* activity edit page: control */
$string['control_edit'] = 'Steuerung';
$string['control_edit_title'] = 'Steuerungs Optionen';
$string['reset_progress_heading'] = 'Fortschritt zurücksetzen';
$string['reset_progress_button'] = 'Fortschritt zurücksetzen';
$string['reset_progress_confirm_title'] = 'Bestätigung Fortschritt zurücksetzen';
$string['reset_progress_confirm_question'] = 'Möchten Sie wirklich den Fortschritt zurücksetzen (Highscores etc.)? Dieser Prozess kann nicht rückgängig gemacht werden.';

/* course reset */
$string['course_reset_include_progress'] = 'Fortschritt zurücksetzen (Highscores etc.)';
$string['course_reset_include_topics'] = 'Eingestellte Themen etc. zurücksetzen (Alles wird gelöscht!)';

/* messaging */
$string['messageprovider:match'] = 'Einladung zu einer »Quiz Challenge«';
$string['message_match_invitation_subject'] = '»Quiz Challenge« Einladung';
$string['message_match_invitation_message_html'] = 'Hallo {$a->fullname},<br />

Sie wurden im Moodle-Kurs »{$a->coursename}« zu Runde {$a->roundnumber} der Quiz Challenge »{$a->gamename}« herausgefordert.<br />

<a href="{$a->matchurl}">Klicken Sie hier</a> um zu Ihren Fragen für diese Runde zu gelangen.<br />

Viel Erfolg!';
$string['message_match_invitation_message_plain'] = 'Hallo {$a->fullname},

Sie wurden im Moodle-Kurs »{$a->coursename}« zu Runde {$a->roundnumber} der Quiz Challenge »{$a->gamename}« herausgefordert. 

Klicken Sie auf den folgenden Link um zu Ihren Fragen für diese Runde zu gelangen:
{$a->matchurl}

Viel Erfolg!';
$string['task_validate_rounds'] = 'Verarbeitung geplanter Quiz-Challenge Spielrunden';
$string['task_send_match_invitations'] = 'Versand von Quiz-Challenge Spielrunden-Einladungen';

/* loading screen in vue app */
$string['loading_screen_title'] = 'Quiz Challenge wird geladen...';

/* formatting */
$string['format_date_time'] = 'DD.MM.YYYY, HH:mm';
$string['format_date'] = 'DD.MM.YYYY';
$string['format_time'] = 'HH:mm';
$string['format_time_suffix'] = 'Uhr';

/* admin screen in vue app */
$string['admin_screen_title'] = 'Spiel-Inhalte bearbeiten';
$string['admin_not_allowed'] = 'Sie haben nicht die nötigen Zugriffsrechte, um diese Seite zu betrachten.';
$string['admin_btn_save'] = 'Speichern';
$string['admin_btn_cancel'] = 'Abbrechen';
$string['admin_btn_add'] = 'Hinzufügen';
$string['admin_btn_confirm_publish'] = 'Wirklich Veröffentlichen';
$string['admin_btn_confirm_delete'] = 'Wirklich Löschen';
$string['admin_btn_confirm_stop'] = 'Wirklich Stoppen';
$string['admin_btn_generate'] = 'Generieren';
$string['admin_btn_datepicker_cancel'] = "Abbrechen";
$string['admin_btn_datepicker_submit'] = "Auswählen";
$string['admin_avatar_alt'] = 'Foto von {$a}';
/* admin: rounds */
$string['admin_nav_rounds'] = 'Runden';
$string['admin_rounds_title'] = 'Spielrunden verwalten';
$string['admin_rounds_intro'] = 'Quiz-Challenges laufen in Runden ab. In jeder Runde werden Kursteilnehmer paarweise dazu aufgefordert, Fragen zu beantworten. Wählen Sie eine Zeitspanne aus, in der die Teilnahme an der Runde möglich sein soll. Wer nicht rechtzeitig teilnimmt erhält keine Punkte. Sie können die Quiz-Challenge fortlaufend spielen lassen, oder zu einem von Ihnen bestimmten Zeitpunkt den aktuell bestplatzierten Teilnehmer als Sieger küren.';
$string['admin_rounds_empty'] = 'Sie haben noch keine Runden angelegt. Klicken Sie auf "Hinzufügen" um eine zu erzeugen.';
$string['admin_rounds_list_th_no'] = 'Nr.';
$string['admin_rounds_list_th_name'] = 'Name';
$string['admin_rounds_list_th_timing'] = 'Zeit';
$string['admin_rounds_list_th_actions'] = 'Aktionen';
$string['admin_rounds_list_timing_open'] = 'offen';
$string['admin_rounds_list_timing_from'] = 'Von {$a}';
$string['admin_rounds_list_timing_to'] = 'Bis {$a}';
$string['admin_round_delete_confirm'] = 'Möchten Sie die Runde {$a} wirklich löschen?';
$string['admin_round_stop_confirm'] = 'Möchten Sie die Runde {$a} wirklich vorzeitig beenden? Achtung: Matches mit unbeantworteten Fragen können nicht gewonnen werden.';
$string['admin_round_datepicker_start'] = "Runden-Start auswählen";
$string['admin_round_edit_title_edit'] = 'Runde {$a} bearbeiten';
$string['admin_round_edit_title_add'] = 'Runde {$a} hinzufügen';
$string['admin_round_edit_description'] = 'Fügen Sie neue Fragenkategorien hinzu, die von dieser Runde an und in allen Folgerunden verwendet werden sollen. Sie können auch Fragenkategorien entfernen - diese werden dann ab dieser Runde nicht mehr verwendet. In vorherigen Runden bleiben Sie unverändert bestehen. Bitte beachten Sie, dass bereits gespeicherte Kategorien nicht verändert, sondern nur entfernt werden können.';
$string['admin_round_lbl_name'] = 'Name';
$string['admin_round_categories_title'] = 'Fragenkategorien bearbeiten';
$string['admin_round_msg_saving'] = 'Die Runde wird gespeichert, bitte warten';
$string['admin_round_lbl_category_open'] = 'Kategorie {$a->number} - Seit Runde {$a->round_first_number}';
$string['admin_round_lbl_category_closed_range'] = 'Kategorie {$a->number} - Von Runde {$a->round_first_number} bis {$a->round_last_number}';
$string['admin_round_lbl_category_closed_same'] = 'Kategorie {$a->number} - Nur in Runde {$a->round_first_number}';
$string['admin_round_lbl_category_new'] = 'Kategorie {$a} - Gerade hinzugefügt';
$string['admin_round_lbl_category_please_select'] = 'Kategorie auswählen';
/* admin: round results */
$string['admin_results_loading'] = 'Rundenergebnisse werden geladen';
$string['admin_results_title'] = 'Ergebnisse Runde {$a}';
$string['admin_results_btn_rounds'] = 'Zurück';
$string['admin_results_pending'] = 'Paarungen und Ergebnisse für diese Runde können erst eingesehen werden, wenn die Runde gestartet wurde.';
$string['admin_results_match_score_table_question'] = 'Frage {$a}';
$string['admin_results_match_score_state_finished'] = 'Beendet';
$string['admin_results_match_score_state_ongoing'] = 'Offen';

/* game screen in vue app */
$string['game_screen_title'] = 'Spiele »Quiz Challenge«';
$string['game_not_allowed'] = 'Sie können nicht an einer »Quiz Challenge« teilnehmen.';
$string['game_not_started'] = 'Diese »Quiz Challenge« wurde noch nicht gestartet. Bitte gedulden Sie sich noch, bis die erste Runde beginnt.';
$string['game_qtype_not_supported'] = 'Der Fragentyp »{$a}« wird nicht unterstützt.';
$string['game_loading_question'] = 'Frage wird geladen';
$string['game_btn_continue'] = 'Weiter';
$string['game_btn_show'] = 'Zeigen';
$string['game_btn_answer'] = 'Beantworten';
$string['game_match_step'] = 'Runde {$a->step} / {$a->total}';
$string['game_match_loading'] = 'Match-Daten werden geladen';
$string['game_match_show_error'] = 'Bei der Anzeige des ausgewählten Matches ist etwas schiefgelaufen.';
$string['game_match_versus'] = 'vs.';
$string['game_match_step'] = 'Runde {$a->step} / {$a->total}';
$string['game_match_lbl_open'] = 'offen';
$string['game_match_lbl_won'] = 'gewonnen';
$string['game_match_lbl_lost'] = 'verloren';
$string['game_match_lbl_question'] = 'Frage {$a} beantworten';
$string['game_match_question_title'] = 'Frage {$a}';
$string['game_round_dates'] = 'Aktiv von <b>{$a->start}</b> bis <b>{$a->end}</b>';
