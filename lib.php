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
 * The file contains definition of 'moodle hooks' handlers.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_catalog_extend_navigation(global_navigation $nav) {

    // TODO: mention about changes in capabilities checks.

    $context = context_system::instance();

    if (has_capability('local/catalog:viewproducts', $context)) {
        $catalognode = new navigation_node([
            'text' => get_string('navcatalogtitle', 'local_catalog'),
            'action' => new moodle_url('/local/catalog/index.php'),
            'icon' => new pix_icon('i/folder', 'Catalog icon'),
        ]);
        $catalognode->showinflatnavigation = true;
        $catalognode->mainnavonly = true;
        $nav->add_node($catalognode);
    }

    if (has_capability('local/catalog:addtoorder', $context)) {
        $ordersnode = new navigation_node([
            'text' => get_string('navorderstitle', 'local_catalog'),
            'action' => new moodle_url('/local/catalog/orders.php'),
            'icon' => new pix_icon('i/folder', 'Products icon'),
        ]);
        $ordersnode->showinflatnavigation = true;
        $ordersnode->mainnavonly = true;
        $nav->add_node($ordersnode);
    }
}

/**
 * Serve the files from the local_catalog file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function local_catalog_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=[]) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    if ($filearea !== 'productimage' && $filearea !== 'producticon') {
        return false;
    }

    require_login();

    if (!has_capability('local/catalog:viewproducts', $context)) {
        return false;
    }

    $itemid = array_shift($args); // The first item in the $args array.

    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_catalog', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
