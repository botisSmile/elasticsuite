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
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'Smile_ElasticsuiteExplain/js/components/form/explain/result/item',
    'MutationObserver'
], function (Component, $, Product) {
    'use strict';

    return Component.extend({
        defaults: {
            showSpinner: true,
            template: "Smile_ElasticsuiteExplain/form/element/explain-results",
            refreshFields: {},
            maxRefreshInterval: 200,
            imports: {
                formData: "${ $.provider }:data"
            },
            messages : {
                showMore : $.mage.__('Show more')
            }
        },

        initialize: function ()
        {
            this._super();

            this.products           = [];
            this.synonyms           = [];
            this.optimizers         = [];
            this.searchPositions    = {};
            this.countTotalProducts = 0;
            this.pageSize           = parseInt(this.pageSize, 10);
            this.currentSize        = this.pageSize;

            this.observe([
                'products',
                'countTotalProducts',
                'currentSize',
                'loading',
                'showSpinner',
                'queryText',
                'synonyms',
                'optimizers'
            ]);
        },

        refreshProductList: function () {
            if (this.refreshRateLimiter !== undefined) {
                clearTimeout();
            }

            this.refreshRateLimiter = setTimeout(function () {
                this.value({queryText : this.queryText()});
                this.loading(true);

                var formData = this.formData;
                formData['page_size'] = this.currentSize();

                $.post(this.loadUrl, this.formData, this.onProductListLoad.bind(this));
            }.bind(this), this.maxRefreshInterval);
        },

        onProductListLoad: function (loadedData) {

            if (loadedData.search_position) {
                this.searchPositions = loadedData.search_position;
            }

            if (loadedData.products) {
                var products = loadedData.products.map(this.createProduct.bind(this));

                this.products(products);
                this.countTotalProducts(parseInt(loadedData.size, 10));
                this.currentSize(Math.max(this.currentSize(), this.products().length));
            }

            if (loadedData.synonyms) {
                this.synonyms(loadedData.synonyms);
            }

            if (loadedData.optimizers) {
                this.optimizers(loadedData.optimizers);
            }

            this.loading(false);
        },

        createProduct: function (productData) {
            productData.priceFormat = this.priceFormat;
            if (this.searchPositions.hasOwnProperty(productData.id)) {
                productData.position = this.searchPositions[productData.id];
            }

            return new Product({data : productData});
        },

        hasProducts: function () {
            return (this.products().length > 0);
        },

        hasMoreProducts: function () {
            return this.products().length < this.countTotalProducts();
        },

        showMoreProducts: function () {
            this.currentSize(this.currentSize() + this.pageSize);
            this.refreshProductList();
        },

        hasSynonyms: function () {
            return (this.synonyms().length > 0);
        },

        hasOptimizers: function () {
            return (this.optimizers().length > 0);
        }
    });
});
