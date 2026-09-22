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
                type: 'reepay_klarna',
                component: 'Radarsofthouse_Reepay/js/view/payment/method-renderer/reepay_klarna'
            }
        );
        return Component.extend({});
    }
);