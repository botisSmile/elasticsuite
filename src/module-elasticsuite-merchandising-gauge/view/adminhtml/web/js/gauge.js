/**
 * @api
 */
define([
    'ko',
    'underscore',
    'jquery',
    'Magento_Ui/js/lib/view/utils/async',
    'uiCollection',
    'uiComponent',
    'mage/translate'
], function (ko, _, $, async, Collection, Component, $t) {

    return Component.extend({
        defaults: {
            rootSelector: '.elasticsuite-admin-product-sorter',
            productsSelector : '.elasticsuite-admin-product-sorter .product-list-item',
            productsMemento: [],
            maxRefreshInterval: 1000,
            dimensions: [],
            dimension: null,
            showSpinner: true,
            automaticRefresh: true,
            imports: {
                products: "${ $.productsProvider }:products",
                editPositions: "${ $.productsProvider }:editPositions",
                blacklistedProducts: "${ $.productsProvider }:blacklistedProducts"/* ,
                loadUrl: "${ $.provider }:data.product_sorter_gauge_load_url" */,
                formData: "${ $.productsProvider }:formData",
                search: "${ $.productsProvider }:search",
                previewSize: "${ $.productsProvider }:pageSize",
                currentSize: "${ $.productsProvider }:currentSize",
                excludedPreviewFields: "${ $.productsProvider }:excludedPreviewFields"
            },
            listens: {
                "${ $.productsProvider }:products" : "refreshProducts",
                "${ $.productsProvider }:editPositions": "refreshMeasures",
                "${ $.productsProvider }:blacklistedProducts" : "refreshMeasures"
            },
            modules: {
                provider: '${ $.provider }'
            }
        },
        initialize: function () {
            this._super();
            console.log("----------------------------");
            /*
            console.log(this.productsProvider);
            console.log(this.products);
            console.log(this.name);
            console.log(this);
            */
            console.log("----------------------------");
            this.observe(['loading', 'dimensions', 'dimension', 'automaticRefresh']);
            this.productsMemento = this.products;
            this.waitContent();
            /*
                ##
                * Applies DOM watcher for the
                * content element rendering.
                *
                * @returns {TimelineView} Chainable.
                ##
                waitContent: function () {
                    $.async({
                        selector: this.selectors.content,
                        component: this.model
                    }, this.initContent);

                    return this;
                },

                 ##
                 * Initializes timelines' content element.
                 *
                 * @param {HTMLElement} content
                 * @returns {TimelineView} Chainable.
                 ##
                initContent: function (content) {
                    this.$content = content;

                    $(content).on('scroll', this.onContentScroll);
                    $(window).on('resize', this.onWindowResize);

                    $.async(this.selectors.item, content, this.initItem);
                    $.async(this.selectors.event, content, this.onEventElementRender);
                    $.async(this.selectors.timeUnit, content, this.initTimeUnit);

                    this.refresh();

                    return this;
                },
            */
        },
        waitContent: function () {
            async.async({
                selector: this.productsSelector
            }, this.initContent);

            return this;
        },
        initContent: function (content) {
            console.log(content)
        },
        refreshProducts: function (data) {
            console.log("-P-P-P-P-P-P-P-P-P-P-P-P-P-P");
            console.log("refreshProducts");
            /*
            console.log(this.products);
            console.log(data);
            */
            // Ignore search contexts.
            if (this.search === "") {
                // this.productsMemento = this.products;
                this.productsMemento = data;
            }
            console.log("-P-P-P-P-P-P-P-P-P-P-P-P-P-P");
        },
        refreshMeasures: function (data) {
            if (this.automaticRefresh()) {
                this.refresh();
            }
        },
        refresh: function () {
            console.log("-^-^-^-^-^-^-^-^-^-^-^-^-^-^");
            console.log("refreshMeasures");

            /*
            console.log(data);
            console.log(this.products);
            console.log(this.editPositions);
            console.log(this.blacklistedProducts);
            */
            console.log("-^-^-^-^-^-^-^-^-^-^-^-^-^-^");

            if (this.refreshRateLimiter !== undefined) {
                clearTimeout();
            }

            this.loading(true);

            this.refreshRateLimiter = setTimeout(function () {
                console.log("-T-T-T-T-T-T-T-T-T-T-T-T-T-T");
                /*
                console.log(this.products);
                console.log(this.productsMemento);
                */
                /*
                console.log(this.editPositions);
                console.log(this.blacklistedProducts);
                console.log(this.formData);
                */
                var formData = this.prepareFormData(this.formData);

                // 1) editPositions data (Taken from product-sorter.js)
                Object.keys(this.editPositions).forEach(function (productId) {
                    formData['product_position[' + productId + ']'] = this.editPositions[productId];
                }.bind(this));

                // 2) blacklistedProducts
                formData['blacklisted_products'] = this.blacklistedProducts;
                formData['dimension'] = $(this.rootSelector + ' .global-gauge .dimension').val();

                formData['preview_size'] = this.previewSize;
                formData['page_size'] = this.currentSize;

                console.log(this.formData);

                // TODO ribay@smile.fr : link this.enabled to this.loadUrl not being null
                if (this.enabled) {
                    this.loadXhr = $.post(this.loadUrl, this.formData, this.onMeasuresLoad.bind(this));
                }
                console.log("-T-T-T-T-T-T-T-T-T-T-T-T-T-T");
            }.bind(this), this.maxRefreshInterval);
        },
        onMeasuresLoad: function (loadedData) {
            console.log(loadedData);

            if (typeof loadedData.available_dimensions !== 'undefined') {
                // Update gauge dimensions.
                this.dimensions(loadedData.available_dimensions);
            }

            if (typeof loadedData.dimension !== 'undefined') {
                this.dimension(loadedData.dimension);
            }

            if (typeof loadedData.score !== 'undefined') {
                // Update products rendering.
                Object.keys(loadedData.score.products).forEach(function (productId) {
                    var product = this.productsSelector + '[data-product-id=' + productId + ']';
                    var scoreValue = loadedData.score.products[productId].value;
                    var scoreLabel = String(loadedData.available_dimensions[loadedData.dimension].valueLabelPattern)
                        .replace('{count}', Math.round(scoreValue).toString());
                    var scorePercent = loadedData.score.products[productId].percent;
                    var content = '<span class="dot" title="' + scoreLabel + '" style="background-position: ' + scorePercent + '%"></span></div>';
                    $(product).find('.performance-score').html(content);
                }.bind(this));

                // Update global gauge.
                var minScore = loadedData.score.range.min,
                    maxScore = loadedData.score.range.max;
                var currentScore = loadedData.score.current;

                var percentage = 100 * Math.min(maxScore, Math.max(minScore, currentScore)) / maxScore;
                $(this.rootSelector + ' .global-gauge .progressbar .meter').width(
                    percentage.toFixed(0) + ".01%"
                );
            }

            this.loading(false);
        },
        getDimensions: function () {
            var dimensions = this.dimensions();

            return _.values(dimensions);
        },
        hasDimensions: function () {
            var dimensions = this.dimensions();

            return !_.isEmpty(dimensions);
        },
        dimensionChanged: function (obj, event) {
            if (event.originalEvent) {
                // User change.
                this.refreshMeasures();
            } else {
                // Program change : do nothing.
            }
        },
        hasAutomaticRefresh: function () {
            console.log(' --- hasAutomaticRefresh --- ');

            return this.automaticRefresh();
        },
        toggleAutomaticRefresh: function () {
            console.log(' --- toggleAutomaticRefresh --- ');
            this.automaticRefresh(!this.automaticRefresh());
            console.log(this.automaticRefresh());
            if (this.automaticRefresh()) {
                this.refreshMeasures();
            }
        },
        applyBestOrdering: function () {
            console.log(' - - - applyBestOrdering  - - - ');
        },
        prepareFormData: function (formData) {
            if (this.excludedPreviewFields) {
                Object.keys(this.excludedPreviewFields).forEach(function (fieldName) {
                    if (formData.hasOwnProperty(fieldName) && formData[fieldName] !== null) {
                        formData[fieldName] = null;
                    }
                });
            }

            return formData;
        }
    });
});
