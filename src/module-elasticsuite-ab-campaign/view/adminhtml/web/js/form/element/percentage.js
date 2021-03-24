/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteAbCampaign
 * @author    Pierre Le maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'jquery'
], function (Element, $) {
    'use strict';

    return Element.extend({
        defaults: {
            percentage_pair: ''
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();
            $(document).on('change', '[name="' + this.percentage_pair + '"]', this.updatePercentage.bind(this));

            return this;
        },

        updatePercentage: function (e) {
            var percentage = parseFloat($(e.currentTarget).val()),
                element = $('[name="' + this.inputName + '"]'),
                newValue = parseFloat(100 - parseFloat(percentage));
            if (percentage >= 0 && percentage <= 100 && element.val() != newValue) {
                element.val(newValue).trigger('change');
            }
        }
    });
});
