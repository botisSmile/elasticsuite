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
    'mage/translate'
], function (Component, $, priceUtil, Item) {

    'use strict';

    return Item.extend({

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
            return $.mage.__("Score boosted by %s").replace('%s', this.data.boosts.weight);
        },

        getBoostsOperationLabel : function () {
            return this.data.boosts.operator + " ( " + parseFloat(this.data.boosts.weight).toFixed(5) + " )";
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

        getFormattedScore : function (score) {
            return parseFloat(score).toFixed(5);
        },

        getMatchesTotal : function () {
            return this.data.matches.map(function (item) {
                return parseFloat(item.score);
            }).reduce(function (a, b) {
                return a + b;
            });
        }
    });

});
