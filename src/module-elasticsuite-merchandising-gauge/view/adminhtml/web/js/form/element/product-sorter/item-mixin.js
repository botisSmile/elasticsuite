define([
    'jquery'
], function ($) {
    return function (originalComponent) {
        return originalComponent.extend({
            getPerformanceScore: function () {
                console.log('getPerformanceScore');
                return this.data.performanceScore;
            },
            setPosition : function (position) {
                this._super(position);
                console.log('setPosition (test)');
            }
        });
    };
});
