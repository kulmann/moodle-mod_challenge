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
 * Class category
 *
 * @package    mod_challenge\model
 * @copyright  2020 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category extends abstract_model {

    /**
     * @var int The id of the game instance.
     */
    protected $game;
    /**
     * @var int The id of the first round this category is active for.
     */
    protected $round_first;
    /**
     * @var int The id of the last round this category is active for.
     */
    protected $round_last;
    /**
     * @var int The id of the moodle question category.
     */
    protected $mdl_category;
    /**
     * @var bool Whether or not to include subcategories when choosing a question.
     */
    protected $subcategories;

    /**
     * category constructor.
     */
    function __construct() {
        parent::__construct('challenge_categories', 0);
        $this->game = 0;
        $this->round_first = 0;
        $this->round_last = 0;
        $this->mdl_category = 0;
        $this->subcategories = true;
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
        $this->id = isset($data['id']) ? $data['id'] : 0;
        $this->game = $data['game'];
        $this->round_first = $data['round_first'];
        $this->round_last = isset($data['round_last']) ? $data['round_last'] : 0;
        $this->mdl_category = $data['mdl_category'];
        $this->subcategories = isset($data['subcategories']) ? ($data['subcategories'] == 1) : false;
    }

    /**
     * @return int
     */
    public function get_game(): int {
        return $this->game;
    }

    /**
     * @param int $game
     */
    public function set_game(int $game) {
        $this->game = $game;
    }

    /**
     * @return int
     */
    public function get_round_first(): int {
        return $this->round_first;
    }

    /**
     * @param int $round_first
     */
    public function set_round_first(int $round_first) {
        $this->round_first = $round_first;
    }

    /**
     * @return int
     */
    public function get_round_last(): int {
        return $this->round_last;
    }

    /**
     * @param int $round_last
     */
    public function set_round_last(int $round_last) {
        $this->round_last = $round_last;
    }

    /**
     * @return bool
     */
    public function is_subcategories(): bool {
        return $this->subcategories;
    }

    /**
     * @param bool $subcategories
     */
    public function set_subcategories(bool $subcategories) {
        $this->subcategories = $subcategories;
    }

    /**
     * @return int
     */
    public function get_mdl_category(): int {
        return $this->mdl_category;
    }

    /**
     * @param int $mdl_category
     */
    public function set_mdl_category(int $mdl_category) {
        $this->mdl_category = $mdl_category;
    }

    /**
     * @return bool
     */
    public function includes_subcategories(): bool {
        return $this->subcategories;
    }

    /**
     * @param bool $subcategories
     */
    public function set_includes_subcategories(bool $subcategories) {
        $this->subcategories = $subcategories;
    }
}
