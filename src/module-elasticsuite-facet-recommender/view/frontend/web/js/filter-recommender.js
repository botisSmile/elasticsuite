/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteFacetRecommender
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

define([
    'jquery',
    'uiComponent',
    'underscore',
    'mage/translate',
    'mage/cookies'
], function ($, Component, _) {
    "use strict";

    return Component.extend({
        defaults: {
            template : "Smile_ElasticsuiteFacetRecommender/filter-recommender",
            title : $.mage.__("Recommended for you in %s :"),
            items: []
        },

        /**
         * Component initialization
         */
        initialize: function () {
            this._super().observe(['items']);
            this.loadItems();
        },

        /**
         * Retrieve data
         *
         * @param callback
         */
        loadItems: function (callback) {
            if (this.baseUrl && this.categoryId && this.cookieConfig && this.cookieConfig['visit_cookie_name'] && this.cookieConfig['visitor_cookie_name']) {
                var categoryId = this.categoryId;
                var userId     = $.mage.cookies.get(this.cookieConfig['visit_cookie_name']);
                var visitorId  = $.mage.cookies.get(this.cookieConfig['visitor_cookie_name']);

                if (userId && visitorId) {
                    // Build URL with format : /V1/elasticsuite-facet-recommender/:userId/:visitorId/:categoryId/ .
                    var urlParts = [
                        userId,
                        visitorId,
                        categoryId
                    ];
                    var ajaxLoadUrl = this.baseUrl + urlParts.join('/');

                    $.get({url: ajaxLoadUrl,cache: true}, function (data) {
                        this.items(data.map(this.prepareItem.bind(this)));
                        if (callback) {
                            return callback();
                        }
                    }.bind(this));
                }
            }
        },

        /**
         * Title
         */
        getTitle : function () {
            return this.title.replace("%s", this.categoryName)
        },

        /**
         * If has items to display.
         *
         * @returns {boolean}
         */
        hasItems: function () {
            return this.items().length > 0;
        },

        /**
         * Prepare items :
         *  - add proper URL according to the context
         *  - flag them as active if it's the case
         *  - @TODO : discard item if not anymore available
         */
        prepareItem: function (item) {
            var url          = new URL(window.location.href);
            var sp           = url.searchParams;
            var value        = item.value;
            var currentValue = sp.getAll(item.name).length ? sp.getAll(item.name) : sp.getAll(item.name + '[]');

            item.active = false;

            if (currentValue.length) {
                sp.delete(item.name);
                sp.delete(item.name + '[]');

                if (currentValue.indexOf(item.value) >= 0) {
                    item.active = true;
                }

                currentValue.forEach(function (cv) {
                    if (cv !== item.value) {
                        sp.append(item.name + '[]', cv);
                    }
                });

                if (!item.active) {
                    sp.append(item.name + '[]', value);
                }

                // Cleanup. If only one value for the '[]' version, reset it to raw.
                if (sp.getAll(item.name + '[]').length === 1) {
                    sp.append(item.name, sp.getAll(item.name + '[]')[0]);
                    sp.delete(item.name + '[]');
                }
            } else {
                sp.append(item.name, value);
            }

            item.url = url.toString();

            return item;
        }
    });
});