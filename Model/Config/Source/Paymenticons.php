<?php

namespace Radarsofthouse\Reepay\Model\Config\Source;

class Paymenticons implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return Reepay payment icons
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'american-express', 'label' => __('American express')],
            ['value' => 'dankort', 'label' => __('Dankort')],
            ['value' => 'diners-club-international', 'label' => __('Diners club international')],
            ['value' => 'discover', 'label' => __('Discover')],
            ['value' => 'forbrugsforeningen', 'label' => __('Forbrugsforeningen')],
            ['value' => 'jcb', 'label' => __('JCB')],
            ['value' => 'maestro', 'label' => __('Maestro')],
            ['value' => 'mastercard', 'label' => __('Mastercard')],
            ['value' => 'mobilepay', 'label' => __('Mobilepay')],
            ['value' => 'unionpay', 'label' => __('Unionpay')],
            ['value' => 'viabill', 'label' => __('Viabill')],
            ['value' => 'anyday', 'label' => __('Anyday')],
            ['value' => 'visa', 'label' => __('Visa')],
            ['value' => 'visa-electron', 'label' => __('Visa electron')],
            ['value' => 'klarna', 'label' => __('Klarna')],
            ['value' => 'klarna-pay-later', 'label' => __('Klarna Pay Later')],
            ['value' => 'klarna-pay-now', 'label' => __('Klarna Pay Now')],
            ['value' => 'klarna-slice-it', 'label' => __('Klarna Slice It')],
            ['value' => 'klarna-direct-bank-transfer', 'label' => __('Klarna Direct Bank Transfer')],
            ['value' => 'klarna-direct-debit', 'label' => __('Klarna Direct Debit')],
            ['value' => 'applepay', 'label' => __('Apple Pay')],
            ['value' => 'paypal', 'label' => __('PayPal')],
            ['value' => 'vipps', 'label' => __('Vipps')],
            ['value' => 'googlepay', 'label' => __('Google Pay')],
            ['value' => 'blik_oc', 'label' => __('BLIK One Click')],
            ['value' => 'giropay', 'label' => __('giropay')],
            ['value' => 'p24', 'label' => __('Przelewy24 (P24)')],
            ['value' => 'swish', 'label' => __('Swish')],
            ['value' => 'ideal', 'label' => __('iDEAL')],
            ['value' => 'verkkopankki', 'label' => __('Verkkopankki')],
            ['value' => 'sepa', 'label' => __('SEPA Direct Debit')],
            ['value' => 'eps', 'label' => __('EPS')],
            ['value' => 'mb-way', 'label' => __('MB Way')],
            ['value' => 'multibanco', 'label' => __('Multibanco')],
            ['value' => 'mybank', 'label' => __('mBank')],
            ['value' => 'payconiq', 'label' => __('Payconiq')],
            ['value' => 'paysafecard', 'label' => __('Paysafecard')],
            ['value' => 'paysera', 'label' => __('Paysera')],
            ['value' => 'postfinance', 'label' => __('PostFinance')],
            ['value' => 'satispay', 'label' => __('Satisfy')],
            ['value' => 'trustly', 'label' => __('Trustly')],
            ['value' => 'wechatpay', 'label' => __('WeChat Pay')]
        ];
    }
}
