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
 * Contains definition of the order persistent class.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\persistent;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use core_user, lang_string, coding_exception;
use core\persistent;

final class order extends persistent {

    public const TABLE = 'local_catalog_orders';

    public const SQL_FIELDS_PREFIX = 'order';

    // TODO: mention adding of the usermodified column to the db table.
    protected static function define_properties() {
        return [
            'userid' => [
                'type' => PARAM_INT,
            ],
            'productid' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    protected function validate_userid($value) {
        if (!core_user::is_real_user($value, true)) {
            return new lang_string('invaliduserid', 'error');
        }

        return true;
    }

    /**
     * @throws coding_exception
     */
    protected function validate_productid($value) {
        if (!product::record_exists($value)) {
            return new lang_string('invalidproductid', 'local_catalog', $value);
        }

        return true;
    }

    /**
     * Named constructor.
     *
     * @param int $userid
     * @param int $productid
     * @return static
     */
    public static function from_primitives(int $userid, int $productid): self {
        $data = new stdClass();
        $data->userid = $userid;
        $data->productid = $productid;

        return new self(0, $data);
    }
}
