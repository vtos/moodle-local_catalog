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
 * This file contains definition of the catalog api class. This class acts as a wrapper for to manage products
 * and orders.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use local_catalog\event\product_deleted;
use coding_exception, dml_exception, renderer_base, context_system, core_user, core_user\fields, core\persistent;
use local_catalog\persistent\product;
use local_catalog\persistent\order;
use local_catalog\exporters\product_exporter;
use local_catalog\exporters\order_exporter;
use local_catalog\event\product_added;

final class catalog_api {

    public static function save_product_from_form(stdClass $formdata): void {

        // This is an extra field in the form, have to check its presence explicitly here.
        if (empty($formdata->image_filemanager)) {
            throw new coding_exception(
                'The \'image_filemanager\' field should be present in the product form definition.'
            );
        }

        // Save draft item id for the image first, as we'll have to remove its field from the data before saving
        //the persistent to avoid 'Unknown property' errors.
        $imagedraftitemid = $formdata->image_filemanager;
        unset($formdata->image_filemanager);

        $product = new product($formdata->id, $formdata);
        $product->save();

        $context = context_system::instance();

        file_save_draft_area_files(
            $imagedraftitemid,
            $context->id,
            'local_catalog',
            'productimage',
            $product->get('id')
        );

        $imagefile = null;

        $fs = get_file_storage();
        if ($imagefiles = $fs->get_area_files(
            $context->id,
            'local_catalog',
            'productimage',
            $product->get('id')
        )) {
            foreach ($imagefiles as $file) {
                if ($file->is_valid_image()) {
                    $imagefile = $file;
                    break;
                }
            }
        }

        if ($imagefile and $tempfile = $file->copy_content_to_temp()) {
            process_new_icon($context, 'local_catalog', 'producticon', $product->get('id'), $tempfile);

            $fs->delete_area_files($context->id, 'local_catalog', 'productimage', $product->get('id'));
        }

        // Trigger a product-added event.
        if (!$formdata->id) { // A flag that a new product is being added.
            $event = product_added::create(
                [
                    'objectid' => $product->get('id'),
                    'context' => $context
                ]
            );
            $event->trigger();
        }
    }

    /**
     * Deletes product and orders related.
     *
     * @param int $productid
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function delete_product(int $productid): void {

        $context = context_system::instance();
        require_capability('local/catalog:manageproducts', $context);

        $product = new product($productid);
        $product->delete();

        // Delete associated orders if any.
        if ($productorders = order::get_records(['productid' => $product->get('id')])) {
            foreach ($productorders as $order) {
                $order->delete();
            }
        }

        // Trigger a product-deleted event.
        $event = product_deleted::create(
            [
                'objectid' => $productid,
                'context' => $context
            ]
        );
        $event->trigger();
    }

    public static function create_order(int $productid): void {
        global $USER;

        $context = context_system::instance();
        require_capability('local/catalog:addtoorder', $context);

        $order = order::from_primitives($USER->id, $productid);
        $order->create();
    }

    /**
     * Fetches products with extra property to track if a product was added to order by the current user.
     *
     * @param int $currentuserid
     * @param renderer_base $output
     * @param int $limitfrom
     * @param int $limitnum
     * @return stdClass[]
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function read_products_catalog(
        int $currentuserid,
        renderer_base $output,
        int $limitfrom,
        int $limitnum
    ): array {
        global $DB;

        $return = [];

        // Need to join orders here to get info about products added to orders.
        $sql = "SELECT p.*, o.id AS addedtoorderid
                  FROM {" . product::TABLE . "} p
             LEFT JOIN {" . order::TABLE . "} o ON (o.productid = p.id AND o.userid = ?)";
        if (!$records = $DB->get_records_sql($sql, [$currentuserid], $limitfrom, $limitnum)) {
            return $return;
        }

        $fs = get_file_storage();
        $context = context_system::instance();

        foreach ($records as $record) {
            // Store the info about this product having been ordered already, then remove it from properties array
            // to instantiate a persistent.
            $addedtoorderalready = !empty($record->addedtoorderid);
            unset($record->addedtoorderid);

            $product = new product(0, $record);

            // Fetch the file image (if exists at all) from a proper file area.
            $imagefile = null;
            if ($imagefiles = $fs->get_area_files(
                $context->id,
                'local_catalog',
                'producticon',
                $product->get('id')
            )) {
                foreach ($imagefiles as $file) {
                    // Fetch the 'biggest' icon to get some flexibility in its dimensions when outputting.
                    if ($file->is_valid_image() and $file->get_filename() == 'f3.jpg') {
                        $imagefile = $file;
                        break;
                    }
                }
            }

            $productexporter = new product_exporter($product,
                [
                    'context' => $context,
                    'imagefile' => $imagefile,
                    'addedtoorder' => $addedtoorderalready,
                ]
            );

            $return[] = $productexporter->export($output);
        }

        return $return;
    }

    /**
     * @param array $filter
     * @return int
     */
    public static function count_orders(array $filter): int {
        if (!empty($filter['user'])) {
            return order::count_records(['userid' => $filter['user']]);
        }

        return order::count_records();
    }

    /**
     * Returns an array of exported products persistents ready for output.
     *
     * @param array $filter
     * @param int $limitfrom
     * @param int $limitnum
     * @param renderer_base $output
     * @return stdClass[] Array of exported products persistents.
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function read_orders(
        array $filter,
        int $limitfrom,
        int $limitnum,
        renderer_base $output
    ): array {

        if (!empty($filter['user'])) {
            return self::read_user_orders($filter['user'], $limitfrom, $limitnum, $output);
        }

        return self::read_all_orders($limitfrom, $limitnum, $output);
    }

    /**
     * @param int $limitfrom
     * @param int $limitnum
     * @param renderer_base $output
     * @return stdClass[]
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function read_all_orders(int $limitfrom, int $limitnum, renderer_base $output): array {
        global $DB;

        $usersfields = fields::for_name()
            ->with_userpic()
            ->get_sql('u', false, 'user')
            ->selects;

        $sql = "SELECT " . self::orders_base_fields_sql() . $usersfields . "
                  FROM {" . order::TABLE . "} o, {" . product::TABLE . "} p, {user} u
                 WHERE o.productid = p.id
                   AND u.id = o.userid
              ORDER BY o.timecreated";
        $recordset = $DB->get_recordset_sql($sql, [], $limitfrom, $limitnum);

        $return = [];
        if (!$recordset->valid()) {
            return $return;
        }

        $context = context_system::instance();

        foreach ($recordset as $record) {
            $order = new order(0, order::extract_record($record, order::SQL_FIELDS_PREFIX));
            $product = new product(0, product::extract_record($record, product::SQL_FIELDS_PREFIX));
            $user = persistent::extract_record($record, 'user');

            $orderexporter = new order_exporter($order,
                [
                    'context' => $context,
                    'user' => $user,
                    'product' => $product,
                ]
            );
            $return[] = $orderexporter->export($output);
        }
        $recordset->close();

        return $return;
    }

    /**
     * @param int $userid
     * @param int $limitfrom
     * @param int $limitnum
     * @param renderer_base $output
     * @return stdClass[]
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function read_user_orders(
        int $userid,
        int $limitfrom,
        int $limitnum,
        renderer_base $output
    ): array {
        global $DB;

        $context = context_system::instance();

        require_capability('local/catalog:addtoorder', $context);

        $sql = self::orders_base_sql();
        $sql .= " AND o.userid = ?";
        $params = [$userid];

        $return = [];
        $recordset = $DB->get_recordset_sql($sql, $params, $limitfrom, $limitnum);
        if (!$recordset->valid()) {
            return $return;
        }

        $user = core_user::get_user(
            $userid,
            fields::for_name()
                ->with_userpic()
                ->get_sql('', false, '', '', false)
                ->selects
        );

        foreach ($recordset as $record) {
            $order = new order(0, order::extract_record($record, order::SQL_FIELDS_PREFIX));
            $product = new product(0, product::extract_record($record, product::SQL_FIELDS_PREFIX));

            $orderexporter = new order_exporter($order,
                [
                    'context' => $context,
                    'user' => $user,
                    'product' => $product,
                ]
            );
            $return[] = $orderexporter->export($output);
        }
        $recordset->close();

        return $return;
    }

    private static function orders_base_fields_sql(): string {
        return order::get_sql_fields('o', order::SQL_FIELDS_PREFIX) .
            ', ' . product::get_sql_fields('p', product::SQL_FIELDS_PREFIX);
    }

    private static function orders_base_sql(): string {
        return "SELECT " . self::orders_base_fields_sql() . "
                  FROM {" . order::TABLE . "} o, {" . product::TABLE . "} p
                 WHERE o.productid = p.id";
    }
}
