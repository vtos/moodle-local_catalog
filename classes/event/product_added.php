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
 * The file contains definition of the 'product added' event.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\event;

defined('MOODLE_INTERNAL') || die();

use core\event\base, moodle_url, coding_exception;

final class product_added extends base {

    /**
     * @inheritDoc
     */
    protected function init() {
        $this->data['objecttable'] = 'local_catalog_products';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * @inheritDoc
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->objectid)) {
            throw new coding_exception('The \'objectid\' must be set.');
        }
    }

    /**
     * @inheritDoc
     */
    public function get_description() {
        return "The user with id '$this->userid' has added a product with id '$this->objectid'.";
    }

    /**
     * @inheritDoc
     */
    public function get_url() {
        return new moodle_url('/local/catalog/products/index.php');
    }

    /**
     * @inheritDoc
     */
    public static function get_name() {
        return get_string('eventproductadded', 'local_catalog');
    }
}
