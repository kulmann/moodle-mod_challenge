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

namespace mod_challenge\model;

defined('MOODLE_INTERNAL') || die();

/**
 * Class participant
 *
 * @package mod_challenge\model
 * @copyright  2021 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class participant {

    /**
     * @var \stdClass
     */
    private $mdl_user;

    public function __construct(\stdClass $mdl_user) {
        $this->mdl_user = $mdl_user;
    }

    /**
     * Returns the id of the user.
     *
     * @return int
     */
    public function get_id() {
        return \intval($this->mdl_user->id);
    }

    /**
     * Returns the firstname of the user.
     *
     * @return string
     */
    public function get_firstname() {
        return $this->mdl_user->firstname;
    }

    /**
     * Returns the lastname of the user.
     *
     * @return string
     */
    public function get_lastname() {
        return $this->mdl_user->lastname;
    }

    /**
     * Returns the concatenated first and lastname of the user.
     *
     * @return string
     */
    public function get_full_name() {
        return implode(" ", [$this->get_firstname(), $this->get_lastname()]);
    }

    /**
     * Gets the ids of rounds that this user attended.
     *
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_attended_round_ids(int $game) {
        global $DB;
        $sql = "SELECT DISTINCT r.id
                FROM {challenge_attempts} a
                JOIN {challenge_questions} q ON a.question = q.id
                JOIN {challenge_matches} m ON q.matchid = m.id
                JOIN {challenge_rounds} r ON m.round = r.id
                WHERE r.game = :game AND a.mdl_user = :user AND a.mdl_answer > 0";
        $params = ['game' => $game, 'user' => $this->get_id()];
        return $DB->get_fieldset_sql($sql, $params);
    }

    /**
     * Generates the picture url of the user, including fallback url if there is none.
     *
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_user_picture_url() {
        // set up page
        $page = new \moodle_page();
        $page->set_url('/user/profile.php');
        $page->set_context(\context_system::instance());
        $renderer = $page->get_renderer('core');

        // Get the user's profile picture and make sure it is correct.
        $userpicture = new \user_picture($this->mdl_user);
        $userpicture->size = true;// will cause f2 size (100px)
        return $userpicture->get_url($page, $renderer)->out(false);
    }
}
