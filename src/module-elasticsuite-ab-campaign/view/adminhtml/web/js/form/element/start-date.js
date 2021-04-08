/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

define([
    'Magento_Ui/js/form/element/date',
    'moment',
    'jquery',
    'mageUtils'
], function (Date, moment, $, utils) {
    'use strict';

    return Date.extend({
        defaults: {
            elementTmpl: 'ui/form/element/date'
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();
            this.manageAvailability();

            return this;
        },

        /**
         * Manage availability.
         */
        manageAvailability: function () {
            this.options.beforeShowDay = function (date) {
                var unavailabilities = this.options.unavailabilities,
                    date = moment(date).format('YYYY-MM-DD'),
                    available = this.getAvailabilityForStartDate(unavailabilities, date);

                return [available, ""];
            }.bind(this);
        },

        /**
         * Get availability for start date.
         *
         * @param unavailabilities
         * @param date
         * @returns {boolean}
         */
        getAvailabilityForStartDate: function (unavailabilities, date) {
            var available = true;
            for (var i = 0; i < unavailabilities.length; i++) {
                var startDate = unavailabilities[i]['start_date'],
                    endDate = unavailabilities[i]['end_date'];
                available = available && !(date >= startDate && date <= endDate);
            }

            return available;
        }
    });
});
