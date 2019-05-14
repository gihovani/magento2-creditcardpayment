define([
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Catalog/js/price-utils',
    'Gg2_CreditCardPayment/js/action/installments',
    'jquery',
    'ko'
], function (
    Component,
    fullScreenLoader,
    priceUtils,
    installments,
    $,
    ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Gg2_CreditCardPayment/payment/cc-form',
            code: 'gg2_creditcardpayment',
            creditCardInstallments: '1',
            creditCardOwner: '',
            allInstallments: ko.observableArray([])
        },

        initialize: function () {
            this._super();
            this.getCcInstallments();
        },

        getCode: function () {
            return this.code;
        },

        isActive: function () {
            return this.getCode() === this.isChecked();
        },

        isInstallmentsActive: function () {
            // return window.checkoutConfig.payment.ccform.installments.active[this.getCode()];
            return true;
        },

        getFormattedPrice: function (price) {
            //todo add format data
            return priceUtils.formatPrice(price);
        },

        getCcInstallments: function () {
            var self = this;
            fullScreenLoader.startLoader();
            $.when(installments()).done(function (transport) {
                self.allInstallments.removeAll();
                _.map(transport, function (value) {
                    if (value.hasOwnProperty('id') && value.hasOwnProperty('value')) {
                        value.label = value.id + 'x ' + self.getFormattedPrice(value.value) +
                            ' (total: ' + self.getFormattedPrice(value.total) + ')';
                    }
                    self.allInstallments.push(value);
                });
            }).always(function () {
                fullScreenLoader.stopLoader();
            });
        },
        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe([
                    'creditCardType',
                    'creditCardExpYear',
                    'creditCardExpMonth',
                    'creditCardNumber',
                    'creditCardVerificationNumber',
                    'creditCardSsStartMonth',
                    'creditCardSsStartYear',
                    'creditCardSsIssue',
                    'selectedCardType',
                    'creditCardInstallments',
                    'creditCardOwner'
                ]);

            return this;
        },
        getData: function () {
            return {
                'method': this.item.method,
                'additional_data': {
                    'cc_cid': this.creditCardVerificationNumber(),
                    'cc_ss_start_month': this.creditCardSsStartMonth(),
                    'cc_ss_start_year': this.creditCardSsStartYear(),
                    'cc_ss_issue': this.creditCardSsIssue(),
                    'cc_type': this.creditCardType(),
                    'cc_exp_year': this.creditCardExpYear(),
                    'cc_exp_month': this.creditCardExpMonth(),
                    'cc_number': this.creditCardNumber(),
                    'cc_installments': this.creditCardInstallments(),
                    'cc_owner': this.creditCardOwner(),
                }
            };
        }
    });
});


