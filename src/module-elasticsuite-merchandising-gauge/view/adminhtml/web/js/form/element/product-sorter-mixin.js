define([
    'jquery',
    'uiLayout'
], function ($, layout) {
    return function (originalComponent) {
        return originalComponent.extend({
            defaults: {
                gaugeConfig: {
                    name: '${ $.name }_gauge',
                    component: 'Smile_ElasticsuiteMerchandisingGauge/js/gauge',
                    template: 'Smile_ElasticsuiteMerchandisingGauge/gauge',
                    productsProvider: '${ $.name }',
                    enabled: true
                },
                modules: {
                    gauge: '${ $.gaugeConfig.name }'
                }
            },
            enableSortableList: function (element, component) {
                this._super(element, component);
                console.log('enableSortableList: refresh global scoring + drawn elements ?');
                if (this.gaugeConfig.enabled) {
                    console.log(this.formData);
                    this.gaugeConfig.loadUrl = this.provider().data['product_sorter_gauge_load_url'];
                    this.gaugeConfig.formKey = this.provider().data['form_key'];
                    layout([this.gaugeConfig]);
                }
            }/*,
            onProductListLoad: function (loadedData) {
                this._super(loadedData);
                console.log('onProductListLoad: refresh global scoring');
            },
            onSortUpdate : function (event, ui) {
                this._super(event, ui);
                console.log('onSortUpdate: update global scoring');
            },
            toggleBlackListed: function (product) {
                this._super(product);
                console.log('toggleBlackListed: update global scoring ?');
            }*/
        });
    };
});
