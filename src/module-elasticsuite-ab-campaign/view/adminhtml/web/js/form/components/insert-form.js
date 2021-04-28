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
    'Magento_Ui/js/form/components/insert-form'
], function ($, Insert) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                optimizerScenarioAListingProvider: '${ $.optimizerScenarioAListingProvider }',
                optimizerScenarioBListingProvider: '${ $.optimizerScenarioBListingProvider }',
                optimizerModal: '${ $.optimizerModalProvider }'
            }
        },

        /**
         * Close modal and reload optimizer.
         *
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            if (!responseData.error) {
                var scenarioType = responseData.data.scenario_type;

                // Close optimizer modal.
                this.optimizerModal().closeModal();

                // Trigger update scenario event.
                $(document).trigger('update-scenario-' + scenarioType, responseData.data);

                // Reload optimizer listing.
                if (scenarioType === 'A') {
                    this.optimizerScenarioAListingProvider().reload({
                        refresh: true
                    });
                } else if (scenarioType === 'B') {
                    this.optimizerScenarioBListingProvider().reload({
                        refresh: true
                    });
                }
            }
        }
    });
});
