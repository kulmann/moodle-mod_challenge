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
 * Challenge invitation renderable.
 *
 * @package    mod_challenge
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_challenge\output;

use coding_exception;
use mod_challenge\model\game;
use mod_challenge\model\match;
use moodle_exception;
use moodle_url;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Forum post renderable.
 *
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @property boolean $viewfullnames Whether to override fullname()
 */
class challenge_match_invitation implements \renderable, \templatable {

    /**
     * The course that the match is in.
     *
     * @var object $course
     */
    protected $course = null;

    /**
     * The course module for the match.
     *
     * @var object $cm
     */
    protected $cm = null;

    /**
     * The game that the match is in.
     *
     * @var game $game
     */
    protected $game = null;

    /**
     * The match itself.
     *
     * @var match $match
     */
    protected $match = null;

    /**
     * The user that is reading the invitation.
     *
     * @var object $userto
     */
    protected $userto = null;

    /**
     * The opponent.
     *
     * @var object $opponent
     */
    protected $opponent = null;

    /**
     * Builds a renderable match invitation
     *
     * @param object $course Course of the match
     * @param object $cm Course Module of the match
     * @param game $game Game the match belongs to
     * @param match $match The match
     * @param object $opponent Inviting user
     * @param object $recipient Recipient of the email - the user to be invited
     */
    public function __construct($course, $cm, $game, $match, $opponent, $recipient) {
        $this->course = $course;
        $this->cm = $cm;
        $this->game = $game;
        $this->match = $match;
        $this->opponent = $opponent;
        $this->userto = $recipient;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $renderer The render to be used for formatting the message
     * @param bool $plaintext Whether the target is a plaintext target
     * @return array Data ready for use in a mustache template
     */
    public function export_for_template(renderer_base $renderer, $plaintext = false) {
        if ($plaintext) {
            return $this->export_for_template_text($renderer);
        } else {
            return $this->export_for_template_html($renderer);
        }
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $renderer The render to be used for formatting the message
     * @return array Data ready for use in a mustache template
     */
    protected function export_for_template_text(renderer_base $renderer) {
        return array(
            'id' => html_entity_decode($this->match->get_id()),
            'coursename' => html_entity_decode($this->get_course_name()),
            'courselink' => html_entity_decode($this->get_course_link()),
            'challengename' => html_entity_decode($this->get_game_name()),
            'challengelink' => html_entity_decode($this->get_game_link()),
            'matchname' => html_entity_decode($this->get_match_name()),
            'matchlink' => html_entity_decode($this->get_match_link()),
            'subject' => html_entity_decode($this->get_subject()),
            'message' => html_entity_decode($this->get_message()),
            'calltoactionname' => html_entity_decode($this->get_call_to_action_name()),
            'calltoactionlink' => html_entity_decode($this->get_call_to_action_link())
        );
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $renderer The render to be used for formatting the message
     * @return array Data ready for use in a mustache template
     */
    protected function export_for_template_html(renderer_base $renderer) {
        return array(
            'id' => $this->match->get_id(),
            'coursename' => $this->get_course_name(),
            'courselink' => $this->get_course_link(),
            'challengename' => $this->get_game_name(),
            'challengelink' => $this->get_game_link(),
            'matchname' => $this->get_match_name(),
            'matchlink' => $this->get_match_link(),
            'subject' => $this->get_subject(),
            'message' => $this->get_message(),
            'calltoactionname' => $this->get_call_to_action_name(),
            'calltoactionlink' => $this->get_call_to_action_link()
        );
    }

    /**
     * Get t name of the course.
     *
     * @return string
     */
    public function get_course_name() {
        return format_string($this->course->shortname, true, array(
            'context' => \context_course::instance($this->course->id),
        ));
    }

    /**
     * Get the link to the course.
     *
     * @return string
     * @throws moodle_exception
     */
    public function get_course_link() {
        $link = new moodle_url(
            '/course/view.php', [
                'id' => $this->course->id,
            ]
        );
        return $link->out(false);
    }

    /**
     * Get the name of the game.
     *
     * @return string
     */
    public function get_game_name() {
        return format_string($this->game->get_name(), true);
    }

    /**
     * Get the link to the game.
     *
     * @return string
     */
    public function get_game_link() {
        $gameid = $this->game->get_id();
        $link = new moodle_url(
            "/mod/challenge/view.php/$gameid"
        );
        return $link->out(false);
    }

    /**
     * Get the name of the match.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_match_name() {
        return get_string('message_match_invitation_calltoaction', 'mod_challenge');
    }

    /**
     * Get the link to the match.
     *
     * @return string
     */
    public function get_match_link() {
        $gameid = $this->game->get_id();
        $matchid = $this->match->get_id();
        $link = new moodle_url(
            "/mod/challenge/view.php/$gameid/game/matches/$matchid"
        );
        return $link->out(false);
    }

    /**
     * Get the subject.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_subject() {
        return get_string('message_match_invitation_subject', 'mod_challenge');
    }

    /**
     * Get the message.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_message() {
        return get_string('message_match_invitation_message', 'mod_challenge');
    }

    /**
     * Get the call to action link name.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_call_to_action_name() {
        return get_string('message_match_invitation_calltoaction', 'mod_challenge');
    }

    /**
     * Get the call to action link.
     */
    public function get_call_to_action_link() {
        return $this->get_match_link();
    }
}
