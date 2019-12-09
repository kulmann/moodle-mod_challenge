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

use context;
use core\external\exporter;
use renderer_base;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class mdl_user_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mdl_user_dto extends exporter {

    /**
     * @var stdClass
     */
    protected $mdl_user;
    /**
     * @var string
     */
    protected $ids;

    /**
     * mdl_user_dto constructor.
     *
     * @param stdClass $mdl_user
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct($mdl_user, context $context) {
        $this->mdl_user = $mdl_user;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'moodle user id',
            ],
            'firstname' => [
                'type' => PARAM_TEXT,
                'description' => 'first name of the user',
            ],
            'lastname' => [
                'type' => PARAM_TEXT,
                'description' => 'last name of the user',
            ],
            'image' => [
                'type' => PARAM_TEXT,
                'description' => 'url of the profile image',
            ],
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        return [
            'id' => \intval($this->mdl_user->id),
            'firstname' => $this->mdl_user->firstname,
            'lastname' => $this->mdl_user->lastname,
            'image' => $this->mdl_user->picture,
        ];
    }
}
