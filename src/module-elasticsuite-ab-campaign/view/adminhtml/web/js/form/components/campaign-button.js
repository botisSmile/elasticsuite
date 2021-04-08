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
    'Magento_Ui/js/form/components/button',
    'underscore'
], function ($, Button, _) {
    'use strict';

    return Button.extend({
        defaults: {
            entityId: null,
            campaignId: null,
            scenarioType: null,
            scenarioAOptimizerCount: 0,
            scenarioBOptimizerCount: 0,
            listens: {
                entity: 'changeVisibility'
            }
        },

        initialize: function () {
            this._super();
            // Hide button if there is an optimizer in the scenario.
            if (this.scenarioBOptimizerCount > 0 || this.scenarioAOptimizerCount > 0) {
                this.visible(false);
            }

            // Update visibility when optimizers in the scenario are updated.
            $(document).on('update-scenario-' + this.scenarioType, this.updateVisibility.bind(this));

            return this;
        },

        /**
         * Apply action on target component, and add data to the action.
         *
         * @param {Object} action - action configuration
         */
        applyAction: function (action) {
            if (action.params && action.params[0]) {
                action.params[0]['entity_id'] = this.entityId;
                action.params[0]['campaign_id'] = this.campaignId;
                action.params[0]['scenario_type'] = this.scenarioType;
            } else {
                action.params = [{
                    'entity_id': this.entityId,
                    'campaign_id': this.campaignId,
                    'scenario_type': this.scenarioType
                }];
            }

            this._super();
        },

        updateVisibility: function (event, data) {
            if (data.hasOwnProperty('optimizer_ids_in_campaign')) {
                this.visible(data.optimizer_ids_in_campaign.length === 0);
            }
        }
    });
});
