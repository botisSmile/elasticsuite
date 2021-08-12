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
    'underscore',
    'Smile_ElasticsuiteExplain/js/components/form/explain/result/item',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'MutationObserver',
    'mage/collapsible'
], function (Component, $, _, Product, modal, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            showSpinner: true,
            template: "Smile_ElasticsuiteExplain/form/element/explain-results",
            refreshFields: {},
            searchContainersWithAutocompletion: {},
            maxRefreshInterval: 250,
            imports: {
                formData: "${ $.provider }:data"
            },
            messages : {
                showMore : $.mage.__('Show more')
            },
            modules: {
                queryField: '${ $.queryField }'
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

            this.products               = [];
            this.synonyms               = [];
            this.optimizers             = [];
            this.productOptimizers      = [];
            this.terms                  = [];
            this.categories             = [];
            this.positions              = {};
            this.countTotalProducts     = 0;
            this.pageSize               = parseInt(this.pageSize, 10);
            this.currentSize            = this.pageSize;
            this.isSpellchecked         = false;
            this.showPreviewButton      = true;
            this.currentRequest         = null;
            this.currentSearchContainer = '';
            this.observeQueryField();

            this.observe([
                'products',
                'countTotalProducts',
                'currentSize',
                'loading',
                'showSpinner',
                'queryText',
                'synonyms',
                'optimizers',
                'productOptimizers',
                'terms',
                'categories',
                'currentProduct',
                'isSpellchecked',
                'showPreviewButton',
                'currentSearchContainer'
            ]);
        },

        observeQueryField: function () {
            $(document).on('input propertychange', '#' + this.queryField().uid, _.debounce(this.onQueryTextUpdate.bind(this), this.maxRefreshInterval));
        },

        refreshProductList: function () {
            if (this.refreshRateLimiter !== undefined) {
                clearTimeout();
            }

            this.refreshRateLimiter = setTimeout(function () {
                this.sendAjax();
            }.bind(this), this.maxRefreshInterval);
        },

        sendAjax: function () {
            this.value({queryText : this.queryText()});
            this.loading(true);

            var formData = this.formData;
            formData['page_size'] = this.currentSize();
            if (this.isSearchContainerWithAutocompletion(this.currentSearchContainer())) {
                formData['q'] = this.queryText();
            }

            this.currentRequest = $.ajax({
                method: "GET",
                url: this.loadUrl,
                data: formData,
                dataType: 'json',
                beforeSend: function() { if (this.currentRequest !== null) { this.currentRequest.abort(); }}.bind(this),
                success: this.onProductListLoad.bind(this)
            });
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

            if (loadedData.terms) {
                this.terms(loadedData.terms);
            }

            if (loadedData.categories) {
                this.categories(loadedData.categories);
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

        hasTerms: function () {
            return (this.terms().length > 0 && this.terms()[0].title);
        },

        hasCategories: function () {
            return (this.categories().length > 0 && this.categories()[0].title);
        },

        getSpellcheckMessage: function () {
            return $.mage.__("No exact results found for: '%1'. The displayed items are the closest matches.")
                .replace('%1', $('[name="query_text_preview"]').val());
        },

        showDetails: function(product) {
            this.getAppliedOptimizers(product);
            this.currentProduct(product);
            if (this.modal === undefined) {
                this.modal = $("#productDetails").modal(this.modalOptions);
            }
            this.modal.modal('openModal');
            $("#highlight-details").collapsible({collateral : {element: '#highlight-details', openedState: '_show'}});
            $("#fields-details").collapsible({collateral : {element: '#fields-details', openedState: '_show'}});
        },

        onContainersUpdate: function (value) {
            this.currentSearchContainer(value);
            this.showPreviewButton(!this.isSearchContainerWithAutocompletion(value));
            this.editQueryInputOnContainersUpdate(value);
        },

        editQueryInputOnContainersUpdate: function (value) {
            var queryInputField = $('#' + this.queryField().uid);
            if (this.isSearchContainerWithAutocompletion(value)) {
                queryInputField.attr('maxLength', this.maxSearchLength);
                this.queryField().notice($t('Type your search'));
                queryInputField.focus();
            } else {
                queryInputField.attr('maxLength', null);
                this.queryField().notice(null);
            }
        },

        isSearchContainerWithAutocompletion: function (searchContainer) {
            if (typeof searchContainer === 'undefined') {
                searchContainer = this.currentSearchContainer();
            }

            return Object.keys(this.searchContainersWithAutocompletion).includes(searchContainer);
        },

        hasQuery: function () {
            return typeof this.queryText() !== 'undefined' && this.queryText().trim().length >= parseInt(this.minSearchLength, 10);
        },

        onQueryTextUpdate: function (event) {
            // On query text change, do action only if we use autocompletion.
            if (this.isSearchContainerWithAutocompletion(this.currentSearchContainer())) {
                var queryInput = $(event.currentTarget);
                this.queryText(queryInput.val());
                queryInput.trigger('change');
                // Send an ajax call if conditions on the query text are met. Otherwise, we reset explain data.
                if (queryInput.val().trim().length >= parseInt(this.minSearchLength, 10)
                    && queryInput.val().trim().length <= parseInt(this.maxSearchLength, 10)) {
                    this.sendAjax();
                } else {
                    this.resetExplainData();
                }
            }
        },

        getAppliedOptimizers: function (product) {
            let isProductBoosted = Object.keys(product.data.boosts).length > 0;
            this.productOptimizers([]);
            // Only get optimizer on product when the query context has optimizer and the product is boosted.
            if (this.hasOptimizers() && isProductBoosted) {
                let data = {
                    store_id: this.formData.store_id,
                    search_container: this.formData.search_container_preview,
                    product_id: product.data.id,
                };
                this.currentRequest = $.ajax({
                    method: "POST",
                    url: this.optimizersDetailsUrl,
                    data: data,
                    dataType: 'json',
                    success: function (data) {
                        let productOptimizers = [];
                        this.optimizers().forEach(function (optimizer) {
                            if (data.includes(optimizer.id)) {
                                productOptimizers.push(optimizer);
                            }
                        }.bind(this));
                        this.productOptimizers(productOptimizers);
                    }.bind(this),
                });
            }
        },

        resetExplainData: function () {
            this.products([]);
            this.synonyms([]);
            this.optimizers([]);
            this.categories([]);
            this.terms([]);
        }
    });
});
