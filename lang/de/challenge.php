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
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
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
$string['level_tile_height'] = 'Kachel-Höhe Themengebiete';
$string['level_tile_height_help'] = 'Wählen Sie für die Darstellung von Themengebieten eine Kachel-Höhe.';
$string['level_tile_height_0'] = 'Flach';
$string['level_tile_height_1'] = 'Normal';
$string['level_tile_height_2'] = 'Hoch';
$string['level_tile_alpha'] = 'Alpha-Overlay Themengebiete';
$string['level_tile_alpha_help'] = 'Zur besseren Lesbarkeit von Text wird ein Overlay über die Themengebiets-Kacheln gelegt. Mit diesem Wert bestimmen Sie die Durchlässigkeit dieses Overlays.';

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

/* loading screen in vue app */
$string['loading_screen_title'] = 'Quiz Challenge wird geladen...';

/* admin screen in vue app */
$string['admin_screen_title'] = 'Spiel-Inhalte bearbeiten';
$string['admin_not_allowed'] = 'Sie haben nicht die nötigen Zugriffsrechte, um diese Seite zu betrachten.';
$string['admin_btn_save'] = 'Speichern';
$string['admin_btn_cancel'] = 'Abbrechen';
$string['admin_btn_add'] = 'Hinzufügen';
$string['admin_btn_confirm_publish'] = 'Wirklich Veröffentlichen';
$string['admin_btn_confirm_delete'] = 'Wirklich Löschen';
$string['admin_btn_generate'] = 'Generieren';
$string['admin_avatar_alt'] = 'Foto von {$a}';
/* admin: levels */
$string['admin_nav_levels'] = 'Themengebiete';
$string['admin_levels_title'] = 'Themengebiet bearbeiten';
$string['admin_levels_none'] = 'Sie haben noch keine Themengebiete angelegt.';
$string['admin_levels_intro'] = 'Sie haben die folgenden Themengebiete für dieses Spiel angelegt. Sie können hier die Daten und Reihenfolge der Themengebiete verändern, oder sie löschen. Bitte beachten Sie, dass das Löschen nur für die zukünftige Turniere Auswirkungen hat.';
$string['admin_level_delete_confirm'] = 'Möchten Sie das Themengebiet »{$a}« wirklich löschen?';
$string['admin_level_title_add'] = 'Themengebiet {$a} erstellen';
$string['admin_level_title_edit'] = 'Themengebiet {$a} bearbeiten';
$string['admin_level_loading'] = 'Lade Themengebiet-Daten';
$string['admin_level_lbl_name'] = 'Name';
$string['admin_level_lbl_bgcolor'] = 'Hintergrund-Farbe';
$string['admin_level_lbl_bgcolor_help'] = 'HEX-Format, mit oder ohne #, im 3er oder 6er Format. Beispiel: #cc0033 oder #c03';
$string['admin_level_lbl_image'] = 'Hintergrund-Bild';
$string['admin_level_lbl_image_drag'] = 'Hochladen via Drag&Drop oder Auswahl';
$string['admin_level_lbl_image_change'] = 'Ändern';
$string['admin_level_lbl_image_remove'] = 'Entfernen';
$string['admin_level_lbl_categories'] = 'Fragen-Zuweisungen';
$string['admin_level_lbl_category'] = 'Kategorie {$a}';
$string['admin_level_lbl_category_please_select'] = 'Kategorie auswählen';
$string['admin_level_msg_saving'] = 'Das Themengebiet wird gespeichert, bitte warten';
/* admin: tournaments */
$string['admin_nav_tournaments'] = 'Turniere';
$string['admin_tournaments_title'] = 'Turniere bearbeiten';
$string['admin_tournaments_title_unpublished'] = 'Geplante Turniere';
$string['admin_tournaments_none_unpublished'] = 'Es gibt keine geplanten Turniere.';
$string['admin_tournaments_intro_unpublished'] = 'Geplante Turniere können bis zu ihrer Veröffentlichung bearbeitet werden.';
$string['admin_tournaments_title_progress'] = 'Laufende Turniere';
$string['admin_tournaments_none_progress'] = 'Es gibt keine laufenden Turniere.';
$string['admin_tournaments_intro_progress'] = 'Laufende Turniere können hier beobachtet werden.';
$string['admin_tournaments_title_finished'] = 'Abgeschlossene Turniere';
$string['admin_tournaments_none_finished'] = 'Es gibt keine abgeschlossenen Turniere.';
$string['admin_tournaments_intro_finished'] = 'Abgeschlossene Turniere können hier ausgewertet werden.';
$string['admin_tournament_publish_confirm'] = 'Möchten Sie das Turnier »{$a}« wirklich veröffentlichen? Die Teilnehmer werden unverzüglich zu ihrer ersten Teilnahme eingeladen.';
$string['admin_tournament_delete_confirm'] = 'Möchten Sie das Turnier »{$a}« wirklich löschen?';
$string['admin_tournament_title_add'] = 'Turnier erstellen';
$string['admin_tournament_title_edit'] = 'Turnier bearbeiten';
$string['admin_tournament_loading'] = 'Lade Turnier-Daten';
$string['admin_tournament_lbl_name'] = 'Name';
$string['admin_tournament_title_matches'] = 'Turnier-Teilnehmer bearbeiten';
$string['admin_tournament_participants_loading'] = 'Lade Turnier-Teilnehmer';
$string['admin_tournament_participants_saving'] = 'Die Matches werden gespeichert, bitte warten';
$string['admin_nav_matches_users'] = 'Teilnehmer auswählen';
$string['admin_nav_matches_pairs'] = 'Matches generieren';
$string['admin_tournament_match_invalid_users'] = 'Mit den ausgewählten Teilnehmern kann kein Turnier gestartet werden. Bitte beachten Sie, dass eine gerade Teilnehmer-Anzahl benötigt wird.';
$string['admin_tournament_match_info_participants'] = '{$a} Teilnehmer ausgewählt';
$string['admin_tournament_match_none_title'] = 'Noch keine Matches';
$string['admin_tournament_match_none_msg'] = 'Sie haben noch keine Teilnehmer-Matches generiert. Bitte klicken Sie auf den »Generieren« Button.';
$string['admin_tournament_match_done_title'] = 'Matches generiert';
$string['admin_tournament_match_done_msg'] = 'Sie haben die folgenden Teilnehmer-Matches generiert. Wenn Sie mit der Zusammenstellung einverstanden sind, können Sie auf »Speichern« klicken.';
$string['admin_tournament_match_table_number'] = 'Nummer';
$string['admin_tournament_match_table_participant'] = 'Teilnehmer';
$string['admin_tournament_title_topics'] = 'Turnier-Themen bearbeiten';
$string['admin_tournament_topics_loading'] = 'Lade Turnier-Themen';
$string['admin_tournament_topics_saving'] = 'Die Themen werden gespeichert, bitte warten';
$string['admin_tournament_topics_lbl_step'] = 'Runde';
$string['admin_tournament_topics_lbl_levels'] = 'Verfügbare Themengebiete';

/* game screen in vue app */
$string['game_screen_title'] = 'Spiele »Quiz Challenge«';
$string['game_not_allowed'] = 'Sie können nicht an einer »Quiz Challenge« teilnehmen.';
$string['game_qtype_not_supported'] = 'Der Fragentyp »{$a}« wird nicht unterstützt.';
$string['game_loading_question'] = 'Frage wird geladen';
$string['game_btn_continue'] = 'Weiter';
$string['game_btn_show'] = 'Zeigen';
$string['game_btn_answer'] = 'Beantworten';
$string['game_tournaments_active_title'] = 'Aktive Turniere';
$string['game_tournaments_active_none'] = 'Sie nehmen derzeit an keinen Turnieren teil.';
$string['game_tournaments_finished_title'] = 'Beendete Turniere';
$string['game_tournaments_finished_none'] = 'Sie haben noch kein Turnier beendet.';
$string['game_tournaments_list_name'] = 'Name';
$string['game_tournament_match_show_error'] = 'Bei der Anzeige des ausgewählten Matches ist etwas schiefgelaufen.';
$string['game_tournament_match_versus'] = 'gegen';
$string['game_tournament_match_step'] = 'Runde {$a->step} / {$a->total}';
$string['game_tournament_match_lbl_open'] = 'offen';
$string['game_tournament_match_lbl_won'] = 'gewonnen';
$string['game_tournament_match_lbl_lost'] = 'verloren';
