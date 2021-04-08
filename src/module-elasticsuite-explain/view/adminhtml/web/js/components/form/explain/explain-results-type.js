/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, Select, modal) {
    'use strict';

    return Select.extend({

        /**
         * Component initializing
         *
         * @returns {exports}
         */
        initialize: function () {
            this._super();

            this.onUpdate(this.value());
            return this;
        },

        /**
         * Hide/Show fields depending of the type of container selected for previewing.
         *
         * @param value Current Value
         * @returns {*}
         */
        onUpdate: function (value) {
            var isFulltext = this.isFulltextContainer(value);

            var depends = uiRegistry.filter('depends = ' + this.index);
            depends.forEach(function (element) {
                if (element.fulltext !== undefined) {
                    var visible = element.fulltext === isFulltext;
                    element.setVisible(visible);
                    if (!visible) {
                        element.value(null);
                    }
                }
            }, this);

            return this._super();
        },

        /**
         * Check if currently selected container is a fulltext one or not.
         *
         * @param currentContainer A search request container
         * @returns {boolean}
         */
        isFulltextContainer: function(currentContainer) {

            var result = false;

            this.options().forEach(function(container) {
                if (currentContainer === container.value) {
                    result = (container.fulltext !== undefined) && (container.fulltext === true);
                }
            }, this);

            return result;
        },

        /**
         * Prevent previewing containers not currently attached to the optimizer.
         *
         * @param searchContainers Current value of search_container field
         * @returns {void}
         */
        onContainersUpdate: function(searchContainers) {
            var options = [];

            if (searchContainers.length === 0) {
                this.disabled(true);
            }

            if (searchContainers.length > 0) {
                this.disabled(false);
                this.initialOptions.forEach(function (option) {
                    if (searchContainers.indexOf(option.value) !== -1) {
                        options.push(option);
                    }
                }, this);

                this.options(options);
            }
        }
    });
});
