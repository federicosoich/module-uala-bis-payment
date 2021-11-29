define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function ($,Component,urlBuilder) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'FS_UalaBis/payment/ualabis',
                redirectAfterPlaceOrder: false
            },
            afterPlaceOrder: function (url) {
                window.location.replace(urlBuilder.build('ualabis/payment/redirect/'));
            },
            getMessage: function(){
                return window.checkoutConfig.payment.ualabis.message;
            }
        });
    }
);