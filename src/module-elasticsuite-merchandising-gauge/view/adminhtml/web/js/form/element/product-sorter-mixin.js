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
                if (this.gaugeConfig.enabled) {
                    this.gaugeConfig.loadUrl = this.provider().data['product_sorter_gauge_load_url'];
                    this.gaugeConfig.formKey = this.provider().data['form_key'];
                    layout([this.gaugeConfig]);
                }
            }
        });
    };
});
