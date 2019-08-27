/**
 * @api
 */
define([
    'ko',
    'underscore',
    'jquery',
    'uiCollection',
    'uiComponent'
], function (ko, _, $, Collection, Component) {

    return Component.extend({
        defaults: {
            rootSelector: '${ $.productsProvider }',
            /*
            rootSelector: '${ $.columnsProvider }:.admin__data-grid-wrap',
            tableSelector: '${ $.rootSelector } -> table.data-grid',
            mainTableSelector: '[data-role="grid"]',
            columnSelector: '${ $.tableSelector } thead tr th',
            noSelectClass: '_no-select',
            hiddenClass: '_hidden',
            fixedX: false,
            fixedY: true,
            minDistance: 2,
            columns: []
            */
            productsMemento: [],
            maxRefreshInterval: 1000,
            imports: {
                products: "${ $.productsProvider }:products",
                editPositions: "${ $.productsProvider }:editPositions",
                blacklistedProducts: "${ $.productsProvider }:blacklistedProducts"/* ,
                loadUrl: "${ $.provider }:data.product_sorter_gauge_load_url" */,
                formData: "${ $.productsProvider }:formData",
                search: "${ $.productsProvider }:search",
                pageSize: "${ $.productsProvider }:pageSize",
                excludedPreviewFields: "${ $.productsProvider }:excludedPreviewFields"
            },
            listens: {
                /* "${ $.productsProvider }:products" : "refreshMeasures", */
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
            this.observe(['loading']);
            this.productsMemento = this.products;
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
                */
                console.log(this.productsMemento);
                console.log(this.editPositions);
                /*
                console.log(this.blacklistedProducts);
                */
                console.log(this.formData);

                var formData = this.prepareFormData(this.formData);

                // 1) editPositions data (Taken from product-sorter.js)
                Object.keys(this.editPositions).forEach(function (productId) {
                    formData['product_position[' + productId + ']'] = this.editPositions[productId];
                }.bind(this));

                formData['pageSize'] = this.pageSize;

                console.log(this.formData);

                if (this.enabled) {
                    this.loadXhr = $.post(this.loadUrl, this.formData, this.onMeasuresLoad.bind(this));
                }
                console.log("-T-T-T-T-T-T-T-T-T-T-T-T-T-T");
            }.bind(this), this.maxRefreshInterval);
        },
        onMeasuresLoad: function (loadedData) {
            console.log(loadedData);

            this.loading(false);
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
