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
 * Contains definition of the plugin renderer class.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\output;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use coding_exception, plugin_renderer_base, html_writer, html_table;

final class renderer extends plugin_renderer_base {

    /**
     * Outputs a per-page control element for the products catalog page. This is a separate method, because it
     * instantiates the control element with the configuration, specific for the products catalog page.
     *
     * @throws coding_exception
     */
    public function catalog_products_perpage_control(int $selected): string {
        $perpagecontrol = perpage_control::for_products_catalog($selected);

        return $this->render($perpagecontrol);
    }

    /**
     * See {@link catalog_products_perpage_control()} for the description, in this case the method is used
     * on the products management page.
     *
     * @param int $selected
     * @return string
     * @throws coding_exception
     */
    public function manage_products_perpage_control(int $selected): string {
        $perpagecontrol = perpage_control::for_products_management($selected);

        return $this->render($perpagecontrol);
    }

    /**
     * See {@link catalog_products_perpage_control()} for the description, in this case the method is used
     * on the orders list page.
     *
     * @param int $selected
     * @return string
     * @throws coding_exception
     */
    public function orders_list_perpage_control(int $selected): string {
        $perpagecontrol = perpage_control::for_orders_list($selected);

        return $this->render($perpagecontrol);
    }

    /**
     * @param stdClass[]
     * @return string
     * @throws coding_exception
     */
    public function orders_list(array $orderslist): string {
        if (!$orderslist) {
            return html_writer::div(
                get_string('noorderstoview', 'local_catalog'),
                'alert alert-info'
            );
        }

        $table = new html_table();
        $table->attributes = ['class' => 'generaltable orders-table'];
        $table->head = [
            get_string('orderslistidtitle', 'local_catalog'),
            get_string('orderslistusernametitle', 'local_catalog'),
            get_string('orderslistproductnametitle', 'local_catalog'),
            get_string('orderslisttimeaddedtitle', 'local_catalog'),
        ];
        $table->data = [];
        foreach($orderslist as $order) {
            $row = [
                $order->id,
                $order->userpic . $order->userfullname,
                $order->producttitle,
                userdate($order->timecreated, "%m/%d/%Y, %l:%M %p")
            ];
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    protected function render_perpage_control(perpage_control $control): string {
        global $PAGE;

        return $this->single_select(
            $PAGE->url,
            'perpage',
            $control->options,
            $control->selected,
            '',
            null,
            [
                'label' => $control->label,
            ]
        );
    }
}
