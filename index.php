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
 * Outputs products catalog.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');

use local_catalog\catalog_api;
use local_catalog\output\perpage_control;
use local_catalog\persistent\product;

// Used by paging bar.
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', perpage_control::CATALOG_PRODUCTS_PERPAGE, PARAM_INT);

require_login();

$context = context_system::instance();
require_capability('local/catalog:viewproducts', $context);

$PAGE->set_context($context);

$PAGE->set_url('/local/catalog/index.php', ['perpage' => $perpage]);

$title = get_string('viewproductspagetitle', 'local_catalog');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);

$renderer = $PAGE->get_renderer('local_catalog');

$totalproducts = product::count_records();
// Fetch an array of exported product persistents.
$products = catalog_api::read_products_catalog($USER->id, $renderer, $page * $perpage, $perpage);

$templatecontext = new stdClass();
$templatecontext->canaddtoorder = has_capability(
    'local/catalog:addtoorder',
    context_system::instance()
);
$templatecontext->products = $products;
$templatecontext->cataloghasproducts = !empty($products);

echo $renderer->header();
if ($totalproducts > 0) {
    echo $renderer->catalog_products_perpage_control($perpage);
}
echo $renderer->render_from_template('local_catalog/products_catalog', $templatecontext);
echo $renderer->paging_bar($totalproducts, $page, $perpage, $PAGE->url);
echo $renderer->footer();
