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
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class score_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2021 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class score_dto extends exporter {

    /**
     * @var int
     */
    private $rank;
    /**
     * @var int
     */
    private $score_total;
    /**
     * @var int
     */
    private $score_won;
    /**
     * @var int
     */
    private $score_best;
    /**
     * @var int
     */
    private $matches_won;
    /**
     * @var int
     */
    private $matches_completed;
    /**
     * @var int
     */
    private $mdl_user;
    /**
     * @var string
     */
    private $mdl_user_name;

    /**
     * score_dto constructor.
     *
     * @param int $rank
     * @param int $score_total
     * @param int $score_won
     * @param int $score_best
     * @param int $matches_won
     * @param int $matches_completed
     * @param int $mdl_user
     * @param string $mdl_user_name
     * @param context $context
     *
     * @throws coding_exception
     */
    public function __construct(int $rank, int $score_total, int $score_won, int $score_best, int $matches_won, int $matches_completed, int $mdl_user, string $mdl_user_name, context $context) {
        $this->rank = $rank;
        $this->score_total = $score_total;
        $this->score_won = $score_won;
        $this->score_best = $score_best;
        $this->matches_won = $matches_won;
        $this->matches_completed = $matches_completed;
        $this->mdl_user = $mdl_user;
        $this->mdl_user_name = $mdl_user_name;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'rank' => [
                'type' => PARAM_INT,
                'description' => 'rank',
            ],
            'score_total' => [
                'type' => PARAM_FLOAT,
                'description' => 'sum of all scores',
            ],
            'score_won' => [
                'type' => PARAM_INT,
                'description' => 'sum of all scores where the user won the matches',
            ],
            'score_best' => [
                'type' => PARAM_INT,
                'description' => 'best score from all the matches of the user',
            ],
            'matches_won' => [
                'type' => PARAM_INT,
                'description' => 'number of matches won by the user',
            ],
            'matches_completed' => [
                'type' => PARAM_INT,
                'description' => 'number of matches completed by the user',
            ],
            'mdl_user' => [
                'type' => PARAM_INT,
                'description' => 'moodle user id',
            ],
            'mdl_user_name' => [
                'type' => PARAM_TEXT,
                'description' => 'the name of this user',
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
            'rank' => $this->rank,
            'score_total' => $this->score_total,
            'score_won' => $this->score_won,
            'score_best' => $this->score_best,
            'matches_won' => $this->matches_won,
            'matches_completed' => $this->matches_completed,
            'mdl_user' => $this->mdl_user,
            'mdl_user_name' => $this->mdl_user_name,
        ];
    }
}
