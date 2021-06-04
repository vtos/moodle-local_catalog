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
 * Contains definition of the product exporter.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\exporters;

defined('MOODLE_INTERNAL') || die();

use renderer_base, moodle_url;
use core\external\persistent_exporter;

final class product_exporter extends persistent_exporter {

    protected static function define_class() {
        return \local_catalog\persistent\product::class;
    }

    protected static function define_related() {
        return [
            'context' => 'context',
            'imagefile' => '\\stored_file?',
            'addedtoorder' => 'bool',
        ];
    }

    protected static function define_other_properties() {
        return [
            'imageurl' => [
                'optional' => true,
                'type' => PARAM_URL,
            ],
            'addedtoorder' => [
                'type' => PARAM_BOOL,
            ],
        ];
    }

    protected function get_other_values(renderer_base $output) {
        if ($file = $this->related['imagefile']) {
            $imageurl = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);
        } else {
            $imageurl = (new moodle_url('/local/catalog/pix/imageplaceholder.jpg'))->out(false);
        }

        return [
            'imageurl' => $imageurl,
            'addedtoorder' => $this->related['addedtoorder'],
        ];
    }
}
