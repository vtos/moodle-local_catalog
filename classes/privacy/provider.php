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
 * Contains definition of the Privacy API for the plugin.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\privacy;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use context, context_user, dml_exception;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

/**
 * The class aimed at handling of the user orders data only.
 *
 * @package local_catalog
 */
final class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * @param int $userid
     * @throws dml_exception
     */
    protected static function delete_user_data(int $userid) {
        global $DB;

        $DB->delete_records('local_catalog_orders', ['userid' => $userid]);
    }

    /**
     * @inheritDoc
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_catalog_orders',
            [
                'userid' => 'privacy:metadata:products_orders:userid',
                'productid' => 'privacy:metadata:products_orders:productid',
            ],
            'privacy:metadata:products_orders'
        );

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $userlist->add_from_sql(
            'userid',
            'SELECT userid FROM {local_catalog_orders}',
            []
        );
    }

    /**
     * @inheritDoc
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $sql = "SELECT ctx.id
                  FROM {local_catalog_orders} o
                  JOIN {context} ctx ON ctx.instanceid = o.userid AND ctx.contextlevel = ?
                 WHERE o.userid = ?";

        return (new contextlist())->add_from_sql($sql, [CONTEXT_USER, $userid]);
    }

    /**
     * @inheritDoc
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();
        if (!in_array($context->instanceid, $userlist->get_userids())) {
            return;
        }

        if ($context->contextlevel == CONTEXT_USER) {
            self::delete_user_data($context->instanceid);
        }
    }

    /**
     * @inheritDoc
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER) {
                continue;
            }
            if ($context->instanceid == $userid) {
                self::delete_user_data($context->instanceid);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }

        self::delete_user_data($context->instanceid);
    }

    /**
     * @inheritDoc
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $context = context_user::instance($contextlist->get_user()->id);
        if ($userorders = $DB->get_records('local_catalog_orders', ['userid' => $contextlist->get_user()->id])) {
            foreach ($userorders as $orderrecord) {
                $data = new stdClass();
                $data->userid = $orderrecord->userid;
                $data->productid = $orderrecord->productid;
                $data->timecreated = transform::datetime($orderrecord->timecreated);

                writer::with_context($context)->export_data(
                    [
                        get_string('privacy:metadata:products_orders', 'local_catalog')
                    ],
                    $data
                );
            }
        }
    }
}
