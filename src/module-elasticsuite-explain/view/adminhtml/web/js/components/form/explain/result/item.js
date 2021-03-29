/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

define([
    'uiComponent',
    'jquery',
    'Magento_Catalog/js/price-utils',
    'Smile_ElasticsuiteCatalog/js/form/element/product-sorter/item',
    'ko',
    'mage/translate'
], function (Component, $, priceUtil, Item, ko) {

    'use strict';

    return Item.extend({
        defaults: {
            boostModeMessages : {
                multiply    : $.mage.__('The field matches score is multiplied by the boosting score.'),
                sum         : $.mage.__('The boosting score is added to the field matches score.'),
                avg         : $.mage.__('An average of the field matches and boosting score is computed.'),
                first       : $.mage.__('Only the first computed score is used.'),
                replace     : $.mage.__('Only the boosting score is used, the field matches score is ignored.'),
                max         : $.mage.__('Only the maximum value between the field matches score and the boosting score is used.'),
                min         : $.mage.__('Only the minimum value between the field matches score and the boosting score is used.')
            },
            scoreModeMessages : {
                multiply    : $.mage.__('Boost values are multiplied.'),
                sum         : $.mage.__('Boost values are summed.'),
                avg         : $.mage.__('A weighted arithmetic mean of boost scores is computed.'),
                first       : $.mage.__('Only the first matching boost value is applied.'),
                max         : $.mage.__('Only the maximum boost value is used.'),
                min         : $.mage.__('Only the minimum boost value is used.')
            }
        },

        initialize: function () {
            this._super();

            this.legends = [];
            for (const field in this.data.legends) {
                this.legends.push(this.data.legends[field]);
            }
        },

        getEffectClass : function () {
            if (this.data.boosts.weight < 1) {
                return 'down';
            } else if (this.data.boosts.weight > 1) {
                return 'up';
            }

            return '';
        },

        getScoreLabel : function () {
            return $.mage.__("Score: %s").replace('%s', this.getScore());
        },

        getSort : function () {
            return this.data.sort;
        },

        getPositionLabel : function () {
            return $.mage.__("Manually set at position %s").replace('%s', this.getPosition());
        },

        hasBoosts : function () {
            return (parseInt(this.data.boosts.total, 10) > 0);
        },

        getBoostsLabel : function () {
            return $.mage.__("Score boosted by %s (%num boost(s))")
                .replace('%s', this.data.boosts.weight)
                .replace('%num', this.data.boosts.total);
        },

        getBoostsNumLabel : function () {
            return $.mage.__("(%num boost(s))").replace('%num', this.data.boosts.total);
        },

        getBoostsBoostMode : function () {
            return this.data.boosts.boost_mode;
        },

        getBoostModeDescription : function () {
            if (this.boostModeMessages[this.data.boosts.boost_mode]) {
                return this.boostModeMessages[this.data.boosts.boost_mode];
            }
            return '';
        },

        getBoostsScoreModeDescription : function () {
            if (this.scoreModeMessages[this.data.boosts.score_mode]) {
                return this.scoreModeMessages[this.data.boosts.score_mode];
            }
            return '';
        },

        getBoostsWeight : function () {
            return this.data.boosts.weight;
        },

        getScoreDetailsLabel : function () {
            return $.mage.__("View score details");
        },

        hasMatches : function () {
            return (this.data.matches.length > 0);
        },

        getMatches : function () {
            return this.data.matches;
        },

        getSynonymDescription: function (match) {
            return $.mage.__("Synonym of %1").replace('%1', match.synonym);
        },

        getFormattedScore : function (score) {
            return parseFloat(score).toFixed(5);
        },

        getMatchesTotal : function () {
            return this.data.matches.map(function (item) {
                return parseFloat(item.score);
            }).reduce(function (a, b) {
                return a + b;
            });
        },

        getHighlights : function () {
            return this.data.highlights;
        },

        getLegends : function () {
            return this.legends;
        },

        hasFieldDescriptionToShow : function (field) {
            return this.data.legends.hasOwnProperty(field);
        },

        popFieldDescription : function (field) {
            let description = '';
            if (this.hasFieldDescriptionToShow(field)) {
                description = this.data.legends[field].legend;
                delete this.data.legends[field];
            }
            return description;
        }
    });

});
