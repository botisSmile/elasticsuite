define([
    'jquery'
], function ($) {
    return function (originalComponent) {
        return originalComponent.extend({
            getPerformanceScore: function () {
                return this.data.performanceScore;
            },
            setPosition : function (position) {
                this._super(position);
            },
            getDimensionLabel: function () {

            },
            hasPerformanceScore: function () {
                return false;
            },
            getPerformanceScoreLabel: function () {
                return this.getPerformanceScore() + this.getDimensionLabel();
            },
            getPerformanceScoreStyle: function () {
                return "background-position: " + this.getPerformanceScore() + "%";
            }
        });
    };
});
