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
 * The file contains definition of the external functions in this plugin, which may be called externally or via ajax.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use Exception;
use moodle_exception;
use external_api, external_function_parameters, external_value, external_single_structure, external_warnings;
use local_catalog\catalog_api;

final class products_catalog extends external_api {

    public static function add_product_to_order(int $productid) {
        $warnings = [];

        try {
            catalog_api::create_order($productid);
        } catch(Exception $e) {
            if ($e instanceof moodle_exception) {
                $warningcode = $e->errorcode;
            } else {
                $warningcode = $e->getCode();
            }

            $warnings[] = [
                'warningcode' => $warningcode,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'warnings' => $warnings,
        ];
    }

    public static function add_product_to_order_parameters() {
        return new external_function_parameters(
            [
                'productid' => new external_value(PARAM_INT, 'If of the product being added to the order.'),
            ]
        );
    }

    public static function add_product_to_order_returns() {
        return new external_single_structure(
            [
                'warnings' => new external_warnings(),
            ]
        );
    }
}
