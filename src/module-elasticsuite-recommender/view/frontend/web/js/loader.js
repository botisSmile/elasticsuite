/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {String} url
     * @param {String} container
     * @param {String} redirectUrl
     */
    function loadRecommended(url, container, redirectUrl) {
        var data = {'uenc': redirectUrl};
        $.ajax({
            url: url,
            type: 'post',
            data: data,
            cache: false,
            dataType: 'html',
            showLoader: true
        }).done(function (data) {
            $(container).html(data);
            $(container).trigger('contentUpdated');
        });
    }

    return function (config) {
        loadRecommended(config.recommenderUrl, config.container, config.redirectUrl);
    };
});
