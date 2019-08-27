/**
 * TODO ribay@smile.fr FILE HEADER
 */

'use strict';

// eslint-disable-next-line no-unused-vars
var config = {
    config: {
        mixins: {
            'Smile_ElasticsuiteCatalog/js/form/element/product-sorter': {
                'Smile_ElasticsuiteMerchandisingGauge/js/form/element/product-sorter-mixin': true
            },
            'Smile_ElasticsuiteCatalog/js/form/element/product-sorter/item': {
                'Smile_ElasticsuiteMerchandisingGauge/js/form/element/product-sorter/item-mixin': true
            },
            'Smile_ElasticsuiteCatalogOptimizer/js/components/form/optimizer/optimizer-preview': {
                'Smile_ElasticsuiteMerchandisingGauge/js/components/form/optimizer/optimizer-preview-mixin': true
            }
        }
    }
};
