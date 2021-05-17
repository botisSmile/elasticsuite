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
    'Smile_ElasticsuiteAbCampaign/js/form/components/campaign-button',
    'mage/translate',
    'mage/dataPost'
], function ($, CampaignButton, $t) {
    'use strict';

    return CampaignButton.extend({
        defaults: {
            persistOptimizersUrl: null
        },

        initialize: function () {
            this._super();
            // Hide button if there is an optimizer in the scenario.
            if (this.campaignStatus !== 'complete' || ((this.scenarioBOptimizers.length === 0 && this.scenarioType === 'B') ||
                (this.scenarioAOptimizers.length === 0 && this.scenarioType === 'A'))) {
                this.visible(false);
            }

            return this;
        },

        persistOptimizers: function () {
            var params = {
                action: this.persistOptimizersUrl,
                data: {
                    confirmation: true,
                    confirmationMessage: $t('Are you sure to want to publish optimizers from scenario %1')
                        .replace('%1', this.scenarioType),
                    campaign_id: this.campaignId,
                    scenario_type: this.scenarioType
                }
            };

            return $.mage.dataPost().postData(params);
        }
    });
});
