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
 * Contains definition of the language strings, introduced by the plugin.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Catalog';

// Capabilities descriptions.
$string['catalog:manageproducts'] = 'See list of all products, create new and update/delete existing products';
$string['catalog:viewproducts'] = 'See list of available products';
$string['catalog:addtoorder'] = 'Add a product to order';
$string['catalog:viewallorders'] = 'See all the existing orders';

// Navigation links.
$string['navcatalogtitle'] = 'Catalog';
$string['navorderstitle'] = 'Orders';

// Admin settings.
$string['settingsmanageproductslink'] = 'Manage Products';

// Forms.
$string['productformtitle'] = 'Title';
$string['productformdescription'] = 'Description';
$string['productformimage'] = 'Image';

// Events.
$string['eventproductadded'] = 'Product was added to the catalog';
$string['eventproductdeleted'] = 'Product was removed from the catalog';

// Flash messages.
$string['productdeletedflash'] = 'Product successfully deleted.';
$string['productsavedflash'] = 'Product successfully saved.';

// Products list.
$string['productstableidheader'] = 'ID';
$string['productstabletitleheader'] = 'Product Title';
$string['productstabletimecreatedheader'] = 'Created At';
$string['productstableactionsheader'] = 'Actions';
$string['productedittitle'] = 'Edit';
$string['productdeletetitle'] = 'Delete';

// Orders list.
$string['orderslistidtitle'] = 'ID';
$string['orderslistusernametitle'] = 'User Name';
$string['orderslistproductnametitle'] = 'Product Name';
$string['orderslisttimeaddedtitle'] = 'Time Added';

// Privacy.
$string['privacy:metadata:products_orders'] = 'Information about users orders with certain products.';
$string['privacy:metadata:products_orders:userid'] = 'ID of the user from an order.';
$string['privacy:metadata:products_orders:productid'] = 'ID of the product from an order.';

$string['manageproductspagetitle'] = 'Manage Products';
$string['viewproductspagetitle'] = 'Products Catalog';
$string['vieworderspagetitle'] = 'Orders List';
$string['editproductpagetitle'] = 'Edit Product';
$string['deleteproductpagetitle'] = 'Delete Product';
$string['addproductpagetitle'] = 'Add Product';
$string['addproductbuttonvalue'] = 'Add Product';
$string['confirmdeleteproduct'] = 'Are you sure you want to delete product \'{$a}\'? The orders with this product will be deleted as well.';
$string['noproductsincatalog'] = 'No products in the catalog yet, sorry.';
$string['addtoorder'] = 'Add to order';
$string['noorderstoview'] = 'No orders to view at the moment.';
$string['addedtoorder'] = 'Added to order';
$string['invalidproductid'] = 'Product with id {$a} does not exist in the database.';
$string['addtoordererrormessage'] = 'An error occured when trying to add the product to order.';
$string['productsperpage'] = 'Products per page:';
$string['ordersperpage'] = 'Orders per page:';
