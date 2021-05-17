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
    'Smile_ElasticsuiteAbCampaign/js/form/components/campaign-button'
], function ($, CampaignButton) {
    'use strict';

    return CampaignButton.extend({
        initialize: function () {
            this._super();
            // Hide button if there is an optimizer in the scenario.
            if (this.scenarioBOptimizers.length > 0 || this.scenarioAOptimizers.length > 0 || this.campaignStatus === 'complete') {
                this.visible(false);
            }

            // Update visibility when optimizers in the scenario are updated.
            $(document).on('update-scenario-' + this.scenarioType, this.updateVisibility.bind(this));

            return this;
        },

        updateVisibility: function (event, data) {
            if (data.hasOwnProperty('optimizer_ids_in_campaign')) {
                this.visible(data.optimizer_ids_in_campaign.length === 0);
            }
        }
    });
});
