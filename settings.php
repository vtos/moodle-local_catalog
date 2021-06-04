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
 * The file contains definition of plugin admin settings.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('root',
    new admin_category(
        'catalog',
        get_string('pluginname', 'local_catalog')
    )
);

// Add a link to products management page to the administration section.
$ADMIN->add('catalog',
    new admin_externalpage(
        'manageproducts',
        get_string('settingsmanageproductslink', 'local_catalog'),
    "$CFG->wwwroot/local/catalog/products/index.php",
        'local/catalog:manageproducts'
    )
);
