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
 * Module to add event listeners to elements on the products catalog page.
 *
 * @module     local_catalog/products_catalog
 * @package    local_catalog
 * @copyright  2021 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import * as Notification from 'core/notification';
import * as Str from 'core/str';

export const init = () => {

    const SELECTORS = {
        ADD_TO_ORDER_BUTTONS: '.add-to-order-action'
    };

    const orderButtons = document.querySelectorAll(SELECTORS.ADD_TO_ORDER_BUTTONS);
    orderButtons.forEach((button) => {
        button.addEventListener('click', function() {
            const button = this;

            const productId = button.dataset.productId;
            const request = {
                methodname: 'local_catalog_add_product_to_order',
                args: {
                    'productid': productId
                }
            };

            Ajax.call([request])[0]
                .then((response) => {
                    if (response.warnings.length !== 0) {
                        const strings = [
                            {
                                key: 'error',
                                component: 'core'
                            },
                            {
                                key: 'addtoordererrormessage',
                                component: 'local_catalog'
                            },
                            {
                                key: 'ok',
                                component: 'core'
                            },
                        ];
                        Str.get_strings(strings)
                            .then((langStrings) => {
                                Notification.alert(
                                    langStrings[0],
                                    langStrings[1],
                                    langStrings[2]
                                );
                            })
                            .catch(Notification.exception);

                        return;
                    }

                    const addedNoteElement = button.nextElementSibling;
                    button.remove();
                    addedNoteElement.style.display = 'block';
                })
                .catch(Notification.exception);
        });
    });
};
