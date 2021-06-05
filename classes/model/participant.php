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
class participant extends abstract_model {

    const STATUS_ENABLED = "enabled";
    const STATUS_DISABLED = "disabled";
    const STATUS = [self::STATUS_ENABLED, self::STATUS_DISABLED];

    /**
     * @var \stdClass
     */
    protected $mdl_user;

    /**
     * @var string The status of the user, out of `enabled` and `disabled`
     */
    protected $status;

    public function __construct(\stdClass $mdl_user) {
        parent::__construct('challenge_users', \intval($mdl_user->id));
        $this->mdl_user = $mdl_user;
    }

    /**
     * Apply data to this object from an associative array or an object.
     *
     * @param mixed $data
     *
     * @return void
     */
    public function apply($data) {
        if (\is_object($data)) {
            $data = get_object_vars($data);
        }
        // don't set the id from $data here. Already present from constructor.
        $this->status = isset($data['status']) ? $data['status'] : self::STATUS_ENABLED;
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
                FROM {challenge_matches} m
                JOIN {challenge_rounds} r ON m.round = r.id
                WHERE r.game = :game AND ((m.mdl_user_1 = :user1 AND m.mdl_user_1_completed > 0) OR (m.mdl_user_2 = :user2 AND m.mdl_user_2_completed > 0))";
        $params = ['game' => $game, 'user1' => $this->get_id(), 'user2' => $this->get_id()];
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

    /**
     * @return string
     */
    public function get_status(): string {
        if ($this->status === null) {
            return self::STATUS_ENABLED;
        }
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function set_status(string $status) {
        if (!in_array($status, self::STATUS)) {
            return;
        }
        $this->status = $status;
    }
}
