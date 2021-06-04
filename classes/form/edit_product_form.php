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
 * Contains definition of the edit product form.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\form;

defined('MOODLE_INTERNAL') || die();

use core\form\persistent;

class edit_product_form extends persistent {

    protected static $persistentclass = 'local_catalog\\persistent\\product';

    protected static $foreignfields = ['image_filemanager'];

    /**
     * @inheritDoc
     */
    protected function definition() {
        $form = $this->_form;

        $form->addElement('text', 'title', get_string('productformtitle', 'local_catalog'));
        $form->addRule('title', get_string('err_required', 'form'), 'required', null, 'client');

        $form->addElement('editor', 'description', get_string('productformdescription', 'local_catalog'), ['rows' => 8]);

        $form->addElement('filemanager', 'image_filemanager', get_string('productformimage', 'local_catalog'), null, $this->_customdata['imagefilemanageroptions']);

        // Hidden fields
        $form->addElement('hidden', 'timestart');
        $form->setConstant('timestart', $this->_customdata['timestart']);

        $form->addElement('hidden', 'timeend');
        $form->setConstant('timeend', $this->_customdata['timeend']);

        $this->add_action_buttons(false);
    }

    /**
     *
     * @inheritDoc
     */
    protected function get_default_data() {
        $data = parent::get_default_data();

        $context = \context_system::instance();
        $itemid = $data->id == 0 ? null : $data->id;
//        file_prepare_standard_filemanager($data, 'image', $this->_customdata['imagefilemanageroptions'], $context, 'local_catalog', 'productimage', $itemid);

        $draftitemid = 0;
        file_prepare_draft_area($draftitemid, $context->id, 'local_catalog', 'productimage', $itemid, $this->_customdata['imagefilemanageroptions']);

        return $data;
    }
}
