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
 * Contains definition of the product persistent class.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\persistent;

defined('MOODLE_INTERNAL') || die();

use context_system, coding_exception, dml_exception;
use core\persistent;

final class product extends persistent {

    public const TABLE = 'local_catalog_products';

    public const SQL_FIELDS_PREFIX = 'product';

    /**
     * Performs some housekeeping when deleting a product, like cleaning the file area.
     *
     * @param bool $result
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function after_delete($result) {
        if (!$result) {
            return;
        }

        $fs = get_file_storage();
        $fs->delete_area_files(
            context_system::instance()->id,
            'local_catalog',
            'producticon',
            $this->get('id')
        );
    }

    // TODO: mention adding of the usermodified column to the db table.
    protected static function define_properties() {
        return [
            'title' => [
                'type' => PARAM_RAW,
            ],
            'description' => [
                'type' => PARAM_CLEANHTML,
                'default' => 'Default text.',
            ],
            'descriptionformat' => [
                'type' => PARAM_INT,
                'default' => FORMAT_HTML,
                'choices' => [
                    FORMAT_PLAIN,
                    FORMAT_HTML,
                    FORMAT_MOODLE,
                    FORMAT_MARKDOWN,
                ],
            ],
            'timestart' => [
                'type' => PARAM_INT,
            ],
            'timeend' => [
                'type' => PARAM_INT,
            ],
        ];
    }
}
