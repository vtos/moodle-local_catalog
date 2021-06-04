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
 * This page outputs products catalog.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../config.php');

use local_catalog\output\perpage_control;
use local_catalog\table\products_table;

// Used by paging bar.
$perpage = optional_param('perpage', perpage_control::MANAGE_PRODUCTS_PERPAGE, PARAM_INT);

require_login();

$context = context_system::instance();
require_capability('local/catalog:manageproducts', $context);

$PAGE->set_context($context);

$PAGE->set_url('/local/catalog/products/index.php', ['perpage' => $perpage]);

$title = get_string('manageproductspagetitle', 'local_catalog');
$PAGE->set_title($title);
$PAGE->set_heading($title);
if (has_capability('local/catalog:viewproducts', $context)) {
    $PAGE->navbar->add(get_string('viewproductspagetitle', 'local_catalog'),
        new moodle_url('/local/catalog/index.php'));
}
$PAGE->navbar->add($title);

$addproductbutton = $OUTPUT->single_button(new moodle_url('/local/catalog/products/edit.php'),
    get_string('addproductbuttonvalue', 'local_catalog'), 'get');
$PAGE->set_button($addproductbutton );

$renderer = $PAGE->get_renderer('local_catalog');

$table = new products_table('products-table');
$table->init($PAGE->url);

// No way to get the content as a string before output for this 'flexible' table...
// We want to see if it contains anything to decide if we need per-page control to be output.
ob_start();
$table->out($perpage, false);
$tableoutput = ob_get_clean();

echo $renderer->header();
if ($table->rawdata) { // Does it contain anything?
    echo $renderer->manage_products_perpage_control($perpage);
}
echo $tableoutput;
echo $renderer->footer();
