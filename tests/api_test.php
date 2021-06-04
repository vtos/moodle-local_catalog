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
 * This file contains unit testing of the {@link catalog_api} class. The suit isn't complete, currently
 * it deals with order creation only.
 *
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\tests;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase, context_system, coding_exception, dml_exception;
use local_catalog\persistent\order;
use local_catalog\catalog_api;

final class api_test extends advanced_testcase {

    /**
     * Test the 'normal' flow of creating an order: correct data, permissions set, etc.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_user_can_create_order() {
        $this->resetAfterTest(true);

        $cataloggenerator = $this->getDataGenerator()->get_plugin_generator('local_catalog');

        // Create a user and assign the add-order capability.
        $context = context_system::instance();
        $archroles = get_archetype_roles('user');
        $userrole = array_shift($archroles);
        assign_capability('local/catalog:addtoorder', CAP_ALLOW, $userrole->id, $context);

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $product = $cataloggenerator->create_product();
        catalog_api::create_order($product->get('id'));

        // Expect an order with with the current user id and the generated product id to be persisted in the database.
        $this->assertEquals(
            1,
            order::count_records(
                [
                    'userid' => $user->id,
                    'productid' => $product->get('id'),
                ]
            )
        );
    }

    public function test_user_cannot_create_order_with_no_permissions() {
        $this->resetAfterTest(true);

        $cataloggenerator = $this->getDataGenerator()->get_plugin_generator('local_catalog');

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $product = $cataloggenerator->create_product();

        $this->expectException('required_capability_exception');
        catalog_api::create_order($product->get('id'));
    }

    public function test_user_cannot_create_order_with_previously_ordered_product() {
        $this->resetAfterTest(true);

        $cataloggenerator = $this->getDataGenerator()->get_plugin_generator('local_catalog');

        $context = context_system::instance();
        $archroles = get_archetype_roles('user');
        $userrole = array_shift($archroles);
        assign_capability('local/catalog:addtoorder', CAP_ALLOW, $userrole->id, $context);

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $product = $cataloggenerator->create_product();
        catalog_api::create_order($product->get('id'));

        $this->expectException('dml_write_exception');
        catalog_api::create_order($product->get('id'));
    }

    public function test_user_cannot_create_order_with_non_existent_product() {
        $this->resetAfterTest(true);

        $context = context_system::instance();
        $archroles = get_archetype_roles('user');
        $userrole = array_shift($archroles);
        assign_capability('local/catalog:addtoorder', CAP_ALLOW, $userrole->id, $context);

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException('core\invalid_persistent_exception');
        catalog_api::create_order(1);
    }
}
