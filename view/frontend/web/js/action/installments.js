define([
    'mage/storage',
    'mage/url',
    'Magento_Checkout/js/model/quote'
], function (storage, url, quote) {
    'use strict';

    return function () {
        var urlTo = 'gg2_creditcardpayment/creditcard/Installments/total/' + quote.totals().grand_total;
        return storage.post(url.build(urlTo), false)
    };
});
