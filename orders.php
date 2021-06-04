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
 * Outputs orders list.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');

use local_catalog\catalog_api;
use local_catalog\output\perpage_control;

// Used by paging bar.
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', perpage_control::ORDERS_LIST_PERPAGE, PARAM_INT);

require_login();

$context = context_system::instance();
$canviewallorders = has_capability('local/catalog:viewallorders', $context);

$PAGE->set_context($context);

$PAGE->set_url('/local/catalog/orders.php', ['perpage' => $perpage]);

$title = get_string('vieworderspagetitle', 'local_catalog');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$renderer = $PAGE->get_renderer('local_catalog');

$filter = $canviewallorders ? [] : ['user' => $USER->id];
$totalorders = catalog_api::count_orders($filter);
$orders = catalog_api::read_orders($filter, $page * $perpage, $perpage, $renderer);

echo $renderer->header();
if ($totalorders > 0) {
    echo $renderer->orders_list_perpage_control($perpage);
}
echo $renderer->orders_list($orders);
echo $renderer->paging_bar($totalorders, $page, $perpage, $PAGE->url);
echo $renderer->footer();
