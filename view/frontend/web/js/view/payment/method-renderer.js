define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'ualabis',
                component: 'FS_UalaBis/js/view/payment/method-renderer/ualabis'
            }
        );
        return Component.extend({});
    }
);
