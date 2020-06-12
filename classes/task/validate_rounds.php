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

namespace mod_challenge\task;

use coding_exception;
use core\task\scheduled_task;
use mod_challenge\model\game;

defined('MOODLE_INTERNAL') || die();

/**
 * Class validate_rounds
 *
 * @package    mod_challenge\model
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class validate_rounds extends scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('task_validate_rounds', 'mod_challenge');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        $records = $DB->get_records("challenge");
        mtrace("Checking rounds of " . count($records) . " games in mod_challenge");
        foreach ($records as $record) {
            $game = new game();
            $game->apply($record);
            try {
                $game->validate_rounds();
            } catch (\moodle_exception $ignored) {
            }
        }
    }
}
