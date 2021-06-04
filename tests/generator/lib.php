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
 * This file contains definition of the data generator class for unit testing.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core\invalid_persistent_exception;
use local_catalog\persistent\product;

final class local_catalog_generator extends component_generator_base {

    /**
     * Creates a product persistent and returns the created instance.
     *
     * @return product
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public function create_product(): product {
        $record = new stdClass();
        $record->title = 'Product';
        $record->description = 'Product Description.';
        $record->descriptionformat = FORMAT_HTML;
        $record->timestart = 0;
        $record->timeend = 0;

        $product = new product(0, $record);
        $product->create();

        return $product;
    }
}
