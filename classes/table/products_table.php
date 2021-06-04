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
 * This file contains definition of the table class, used to output products on the products management page.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use stdClass;
use moodle_url, table_sql, html_writer;

class products_table extends table_sql {

    public const DB_TABLE = 'local_catalog_products';

    public function init(moodle_url $baseurl): void {
        global $CFG;

        $this->define_columns(
            [
                'id',
                'title',
                'timecreated',
                'actions',
            ]
        );
        $this->define_headers(
            [
                get_string('productstableidheader', 'local_catalog'),
                get_string('productstabletitleheader', 'local_catalog'),
                get_string('productstabletimecreatedheader', 'local_catalog'),
                get_string('productstableactionsheader', 'local_catalog'),
            ]
        );
        $this->no_sorting('actions');
        $this->define_baseurl($baseurl);
        $this->set_sql('*', $CFG->prefix . self::DB_TABLE, '1');
        $this->is_downloadable(false);
        $this->collapsible(false);
        $this->attributes = ['class' => 'manage-products-table'];
    }

    public function col_timecreated(stdClass $row): string {
        return userdate($row->timecreated, "%m/%d/%Y, %l:%M %p");
    }

    public function col_actions(stdClass $row): string {
        global $OUTPUT;

        $editlinkcontents = $OUTPUT->pix_icon('t/edit', get_string('productedittitle', 'local_catalog'));
        $output = html_writer::link(
            new moodle_url('/local/catalog/products/edit.php', ['product' => $row->id]),
            $editlinkcontents
        );

        $deletelinkcontents = $OUTPUT->pix_icon('t/delete', get_string('productdeletetitle', 'local_catalog'));
        $output .= html_writer::link(
            new moodle_url('/local/catalog/products/edit.php', ['deleteproduct' => $row->id]),
            $deletelinkcontents
        );

        return $output;
    }
}
