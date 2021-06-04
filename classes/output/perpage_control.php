<?php
// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * The file contains definition of the output object, which allows to control number of elements per page when
 * a paging bar is used.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\output;

defined('MOODLE_INTERNAL') || die();

use renderable, coding_exception;

final class perpage_control implements renderable {

    public const CATALOG_PRODUCTS_PERPAGE = 3;

    public const MANAGE_PRODUCTS_PERPAGE = 5;

    public const ORDERS_LIST_PERPAGE = 5;

    /**
     * @var string
     */
    public $label;

    /**
     * @var array
     */
    public $options;

    /**
     * @var int
     */
    public $selected;

    /**
     * Instantiates a per-page control object with the required configuration for the products catalog page.
     *
     * @param int $selected
     * @return perpage_control
     * @throws coding_exception
     */
    public static function for_products_catalog(int $selected): self {
        $return = new self();
        $return->label = get_string('productsperpage', 'local_catalog');
        $return->options = [3 => 3, 6 => 6];
        $return->selected = $selected;

        return $return;
    }

    /**
     * Instantiates a per-page control object with the required configuration for the products management page.
     *
     * @param int $selected
     * @return perpage_control
     * @throws coding_exception
     */
    public static function for_products_management(int $selected): self {
        $return = new self();
        $return->label = get_string('productsperpage', 'local_catalog');
        $return->options = [5 => 5, 10 => 10, 15 => 15, 20 => 20];
        $return->selected = $selected;

        return $return;
    }

    /**
     * Instantiates a per-page control object with the required configuration for the orders page.
     *
     * @param int $selected
     * @return static
     * @throws coding_exception
     */
    public static function for_orders_list(int $selected): self {
        $return = new self();
        $return->label = get_string('ordersperpage', 'local_catalog');
        $return->options = [5 => 5, 10 => 10, 15 => 15, 20 => 20];
        $return->selected = $selected;

        return $return;
    }
}
