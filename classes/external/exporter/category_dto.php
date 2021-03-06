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
use mod_challenge\model\category;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class category_dto
 *
 * @package    mod_challenge\external\exporter
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_dto extends exporter {

    /**
     * @var category
     */
    protected $category;

    /**
     * category_dto constructor.
     *
     * @param category $category
     * @param context $context
     *
     * @throws \coding_exception
     */
    public function __construct(category $category, context $context) {
        $this->category = $category;
        parent::__construct([], ['context' => $context]);
    }

    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'category id',
            ],
            'game' => [
                'type' => PARAM_INT,
                'description' => 'game id',
            ],
            'round_first' => [
                'type' => PARAM_INT,
                'description' => 'id of the first round this category is used in',
            ],
            'round_last' => [
                'type' => PARAM_INT,
                'description' => 'id of the last round this category is used in',
            ],
            'mdl_category' => [
                'type' => PARAM_INT,
                'description' => 'moodle category id',
            ],
            'subcategories' => [
                'type' => PARAM_BOOL,
                'description' => 'whether or not to include sub categories',
            ],
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    protected function get_other_values(renderer_base $output) {
        return $this->category->to_array();
    }
}
