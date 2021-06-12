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

namespace mod_challenge\external\exporter;

use coding_exception;
use context;
use core\external\exporter;
use mod_challenge\model\participant;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class user_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2021 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_dto extends exporter {

    const TYPE_TEACHER = 'teacher';
    const TYPE_PARTICIPANT = 'participant';

    /**
     * @var string The type of user. Can be 'teacher' or 'participant'.
     */
    private $type;
    /**
     * @var participant The user represented as participant object
     */
    private $participant;

    public function __construct(string $type, participant $participant, context $context) {
        $this->type = $type;
        $this->participant = $participant;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'moodle user id',
            ],
            'type' => [
                'type' => PARAM_ALPHA,
                'description' => 'type of user, can be "teacher" or "participant"',
            ],
            'fullname' => [
                'type' => PARAM_TEXT,
                'description' => 'first and last name of the user',
            ],
            'image' => [
                'type' => PARAM_TEXT,
                'description' => 'url of the profile image',
            ],
            'status' => [
                'type' => PARAM_ALPHA,
                'description' => 'current status of the user (' . \implode(', ', participant::STATUS) . ')'
            ],
            'attended_rounds' => [
                'type' => PARAM_NOTAGS,
                'description' => 'comma separated list of round ids the user participated in',
            ]
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        return [
            'id' => $this->participant->get_mdl_user(),
            'type' => $this->type,
            'fullname' => $this->participant->get_full_name(),
            'image' => $this->participant->get_user_picture_url(),
            'status' => $this->participant->get_status(),
            'attended_rounds' => implode(',', $this->participant->get_attended_round_ids()),
        ];
    }
}
