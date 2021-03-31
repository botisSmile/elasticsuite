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
            $(document).on('change', '[name="' + this.start_date_element + '"]', this.updateEndDefaultDate.bind(this));

            return this;
        },

        /**
         * Update end default date after the change of the start date.
         */
        updateEndDefaultDate: function () {
            $('[name="' + this.inputName + '"]').datepicker(
                "option",
                "defaultDate",
                $('[name="' + this.start_date_element + '"]').val()
            );
        },

        /**
         * Manage availability.
         */
        manageAvailability: function () {
            this.options.beforeShowDay = function (date) {
                var unavailabilities = this.options.unavailabilities,
                    date = moment(date).format('YYYY-MM-DD'),
                    available = this.getAvailabilityForEndDate(unavailabilities, date);

                return [available, ""];
            }.bind(this);
        },

        /**
         * Get availability for end date.
         *
         * @param unavailabilities
         * @param date
         * @returns {boolean}
         */
        getAvailabilityForEndDate: function (unavailabilities, date) {
            var startDate = moment($('[name="' + this.start_date_element + '"]').val(), utils.convertToMomentFormat(this.options.dateFormat)).format('YYYY-MM-DD'),
                available = date >= startDate,
                nearestCampaign = null;

            for (var i = 0; i < unavailabilities.length; i++) {
                var startAvailability = unavailabilities[i]['start_date'],
                    endAvailability = unavailabilities[i]['end_date'];
                if (!nearestCampaign && endAvailability > startDate) {
                    nearestCampaign = startAvailability;
                    continue;
                }
                if (nearestCampaign > startAvailability && startAvailability > startDate) {
                    nearestCampaign = startAvailability;
                }
            }

            return available && (nearestCampaign ? date < nearestCampaign : true);
        }
    });
});
