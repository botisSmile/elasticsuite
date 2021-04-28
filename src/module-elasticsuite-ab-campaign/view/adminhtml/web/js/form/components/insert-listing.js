/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/insert-listing'
], function ($, Insert) {
    'use strict';

    return Insert.extend({
        defaults: {
            scenario_type: '',
        },

        initialize: function () {
            this._super();

            // Add scenario type data to the listing provider.
            $(document).on('campaign-optimizer-list-provider-reload', function (event, provider) {
                provider.params.scenario_type = this.scenario_type;
            }.bind(this));

            this.setVisible(false);

            return this;
        },

        render: function (params) {
            if (params && params.hasOwnProperty('scenario_type')) {
                this.scenario_type = params.scenario_type;
            }

            return this._super(params);
        },

        setVisible: function (param) {
            this.visible(param);
        }
    });
});
