/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

define([
    'Magento_Ui/js/grid/columns/actions',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'jquery',
    'mage/translate'
], function (Actions, uiAlert, _, $, $t) {
    'use strict';

    return Actions.extend({
        defaults: {
            scenarioType: null,
            campaignStatus: null,
            optimizerPersisted: null,
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            },
            listens: {
                action: 'onAction'
            },
            ignoreTmpls: {
                fieldAction: true,
                options: true,
                action: true
            }
        },

        initialize: function () {
            this._super();

            $(document).on('update-scenario-' + this.scenarioType, this.changeLabel.bind(this));

            return this;
        },

        getBody: function () {
            // If the campaign status is complete, change the template of the component.
            if (this.campaignStatus === 'complete') {
                if (this.optimizerPersisted) {
                    $.each(this.rows, function (index, row) {
                        if (parseInt(row.optimizer_id) === parseInt(this.optimizerPersisted)) {
                            row.isPersisted(true);
                        }
                    }.bind(this));
                }
                return 'Smile_ElasticsuiteAbCampaign/grid/cells/persist-optimizer-actions';
            }

            return this._super();
        },

        getVisibleActions: function (rowIndex) {
            var rowActions = this.getAction(rowIndex);

            return _.filter(rowActions, this.isActionVisible, this);
        },

        changeLabel: function (event, data) {
            if (data.hasOwnProperty('persist')) {
                this.optimizerPersisted = parseInt(data.persist);
            }
        },

        isPersisted: function (row) {
            return parseInt(this.optimizerPersisted) === parseInt(row.optimizer_id);
        },

        getPersistedLabel: function () {
            return $t('Published');
        },

        /**
         * Reload optimizer listing data source after optimizer delete action.
         *
         * @param {Object} data
         */
        onAction: function (data) {
            if (data.action === 'delete') {
                this.source().reload({
                    refresh: true
                });
            }
        },

        /**
         * Default action callback. Redirects to the specified in action's data url.
         *
         * @param {String} actionIndex - Action's identifier.
         * @param {(Number|String)} recordId - Id of the record associated
         *      with a specified action.
         * @param {Object} action - Action's data.
         */
        defaultCallback: function (actionIndex, recordId, action) {
            if (action.isAjax) {
                this.request(action.href).done(function (response) {
                    var data;

                    if (!response.error) {
                        data = _.findWhere(this.rows, {
                            _rowIndex: action.rowIndex
                        });

                        $(document).trigger('update-scenario-' + response.data.scenario_type, response.data);

                        this.trigger('action', {
                            action: actionIndex,
                            data: data
                        });
                    }
                }.bind(this));

            } else {
                this._super();
            }
        },

        /**
         * Send optimizer listing ajax request
         *
         * @param {String} href
         */
        request: function (href) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href,
                data: {
                    'form_key': window.FORM_KEY
                }
            });

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });
                    }
                })
                .fail(function () {
                    uiAlert({
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        }
    });
});
