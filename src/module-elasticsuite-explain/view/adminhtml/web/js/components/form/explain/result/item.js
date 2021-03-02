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
            if (this.data.effect === -1) {
                return 'down';
            } else if (this.data.effect === 1) {
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
            return true;
        },

        getBoostsLabel : function () {
            return $.mage.__("Score boosted by %s%").replace('%s', 100);
        },

        getScoreDetailsLabel : function () {
            return $.mage.__("View score details");
        },
    });

});
