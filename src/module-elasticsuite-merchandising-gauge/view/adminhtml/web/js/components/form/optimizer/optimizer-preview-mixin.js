define([
    'jquery'
], function ($) {
    return function (originalComponent) {
        return originalComponent.extend({
            onProductListLoad: function (loadedData) {
                this._super(loadedData);
                console.log('onProductListLoad: refresh global scoring ?');
            }
        });
    };
});
