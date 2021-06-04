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
 * This page allows editing and deleting of a product.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/gdlib.php');

use local_catalog\catalog_api;
use local_catalog\form\edit_product_form;
use local_catalog\persistent\product;

$editproductid = optional_param('product', 0, PARAM_INT);
$deleteproductid = optional_param('deleteproduct', 0, PARAM_INT);
$confirmdelete = optional_param('confirmdelete', 0, PARAM_INT);

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = [];
if ($editproductid) {
    $urlparams['product'] = $editproductid;
}
if ($deleteproductid) {
    $urlparams['deleteproduct'] = $deleteproductid;
}
$PAGE->set_url('/local/catalog/products/edit.php', $urlparams);

if ($editproductid) {
    $title = get_string('editproductpagetitle', 'local_catalog');
} elseif ($deleteproductid) {
    $title = get_string('deleteproductpagetitle', 'local_catalog');
} else {
    $title = get_string('addproductpagetitle', 'local_catalog');
}
$PAGE->set_title($title);
$PAGE->set_heading($title);

if (has_capability('local/catalog:viewproducts', $context)) {
    $PAGE->navbar->add(get_string('viewproductspagetitle', 'local_catalog'),
        new moodle_url('/local/catalog/index.php'));
}
$PAGE->navbar->add(get_string('manageproductspagetitle', 'local_catalog'),
    new moodle_url('/local/catalog/products/index.php'));
$PAGE->navbar->add($title);

// Handle product deletion if requested.
if ($deleteproductid) {
    if ($confirmdelete) {
        catalog_api::delete_product($deleteproductid);

        redirect(new moodle_url('/local/catalog/products/index.php'), get_string('productdeletedflash', 'local_catalog'));
    }

    // Fetch the product to have its name for a smarter confirm message.
    $deleteproduct = new product($deleteproductid);

    echo $OUTPUT->header();
    echo $OUTPUT->confirm(
        get_string('confirmdeleteproduct', 'local_catalog', $deleteproduct->get('title')),
        new moodle_url('/local/catalog/products/edit.php', ['deleteproduct' => $deleteproduct->get('id'), 'confirmdelete' => 1]),
        new moodle_url('/local/catalog/products/index.php')
    );
    echo $OUTPUT->footer();
    exit;
}

require_capability('local/catalog:manageproducts', $context);

$product = null;
if ($editproductid) {
    $product = new product($editproductid);
}

$imagefilemanageroptions = [
    'maxfiles' => 1,
    'maxbytes' => $CFG->maxbytes,
    'accepted_types' => 'web_image',
];

$customdata = [
    'persistent' => $product,
    'imagefilemanageroptions' => $imagefilemanageroptions,
    'timestart' => time(),
    'timeend' => 0,
];
$form = new edit_product_form($PAGE->url->out(false), $customdata);

if ($data = $form->get_data()) {
    catalog_api::save_product_from_form($data);
    redirect(
        new moodle_url('/local/catalog/products/index.php'),
        get_string('productsavedflash', 'local_catalog')
    );
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
