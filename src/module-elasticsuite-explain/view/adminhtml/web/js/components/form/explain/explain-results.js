/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'Smile_ElasticsuiteExplain/js/components/form/explain/result/item',
    'Magento_Ui/js/modal/modal',
    'MutationObserver',
    'mage/collapsible'
], function (Component, $, Product, modal) {
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
            },
            modalOptions : {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: $.mage.__('Details')
            }
        },

        initialize: function ()
        {
            this._super();

            this.products           = [];
            this.synonyms           = [];
            this.optimizers         = [];
            this.positions          = {};
            this.countTotalProducts = 0;
            this.pageSize           = parseInt(this.pageSize, 10);
            this.currentSize        = this.pageSize;
            this.isSpellchecked     = false;

            this.observe([
                'products',
                'countTotalProducts',
                'currentSize',
                'loading',
                'showSpinner',
                'queryText',
                'synonyms',
                'optimizers',
                'currentProduct',
                'isSpellchecked'
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

            if (loadedData.positions) {
                this.positions = loadedData.positions;
            }

            if (loadedData.products) {
                var products = loadedData.products.map(this.createProduct.bind(this));

                this.products(products);
                this.countTotalProducts(parseInt(loadedData.size, 10));
                this.currentSize(Math.max(this.currentSize(), this.products().length));
            }

            if (loadedData.hasOwnProperty('is_spellchecked')) {
                this.isSpellchecked(loadedData.is_spellchecked);
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
            if (this.positions.hasOwnProperty(productData.id)) {
                productData.position = this.positions[productData.id];
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
        },

        getSpellcheckMessage: function () {
            return $.mage.__("No exact results found for: '%1'. The displayed items are the closest matches.")
                .replace('%1', $('[name="query_text_preview"]').val());
        },

        showDetails: function(product) {
            this.currentProduct(product);
            if (this.modal === undefined) {
                this.modal = $("#productDetails").modal(this.modalOptions);
            }
            this.modal.modal('openModal');
            $("#highlight-details").collapsible({collateral : {element: '#highlight-details', openedState: '_show'}});
            $("#fields-details").collapsible({collateral : {element: '#fields-details', openedState: '_show'}});
        }
    });
});
