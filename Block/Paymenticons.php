<?php

namespace Radarsofthouse\Reepay\Block;

use Magento\Framework\Exception\NoSuchEntityException;

class Paymenticons extends \Magento\Framework\View\Element\Template
{
    const CUSTOM_ICON_FOLDER = "billwerk/icons/";

    // Mapping with the values in \Radarsofthouse\Reepay\Model\Config\Source\Paymenticons
    const OVERRIDABLE_PAYMENT_ICONS = [
        'forbrugsforeningen',
        'mobilepay',
        'viabill',
        'anyday',
        'klarna',
        'klarna-pay-later',
        'klarna-pay-now',
        'klarna-slice-it',
        'klarna-direct-bank-transfer',
        'klarna-direct-debit',
        'applepay',
        'paypal',
        'vipps',
        'googlepay',
        'blik_oc',
        'giropay',
        'p24',
        'swish',
        'ideal',
        'verkkopankki',
        'sepa',
        'eps',
        'mb-way',
        'multibanco',
        'mybank',
        'payconiq',
        'paysafecard',
        'paysera',
        'postfinance',
        'satispay',
        'trustly',
        'wechatpay'
    ];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get payment icons for credit card payment
     *
     * @return array
     */
    public function getPaymentIcons()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $paymentIconsConfig = $this->scopeConfig->getValue('payment/reepay_payment/payment_icons', $storeScope);
        $useCustomIcon = $this->scopeConfig->getValue('payment/reepay_payment/use_custom_icon', $storeScope);

        if (empty($paymentIconsConfig)) {
            return [];
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $_paymentIcons = explode(',', $paymentIconsConfig);
        $paymentIcons = [];
        foreach ($_paymentIcons as $key => $paymentIcon) {
            $_paymentIcon = $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/' . $paymentIcon . '.png');
            if ($useCustomIcon && in_array($paymentIcon, self::OVERRIDABLE_PAYMENT_ICONS)) {
                $paymentMethod = 'reepay_' . str_replace("-", "", $paymentIcon);
                $customIcon = $this->scopeConfig->getValue('payment/' . $paymentMethod . '/custom_icon', $storeScope);
                if (!empty($customIcon)) {
                    $_paymentIcon = $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
                }
            }

            $paymentIcons[] = $_paymentIcon;
        }

        return $paymentIcons;
    }

    /**
     * Get Viabill payment icon
     *
     * @return string|null
     */
    public function getViabillPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_viabill/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_viabill/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/viabill.png');
    }

    /**
     * Get AnyDay payment icon
     *
     * @return string|null
     */
    public function getAnydayPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_anyday/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_anyday/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/anyday.png');
    }

    /**
     * Get mobile pay payment icon
     *
     * @return string|null
     */
    public function getMobilepayPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_mobilepay/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_mobilepay/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/mobilepay.png');
    }

    /**
     * Get vipps mobile pay payment icon
     *
     * @return string|null
     */
    public function getVippsepaymentPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_vippsepayment/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_vippsepayment/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        try {
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
            if ($currencyCode == 'NOK') {
                return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/vipps.png');
            } elseif (in_array($currencyCode, ['DKK', 'EUR'])) {
                return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/mobilepay.png');
            }
        } catch (NoSuchEntityException $e) {
            return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/mobilepay.png');
        }
    }

    /**
     * Get Applepay payment icon
     *
     * @return string|null
     */
    public function getApplepayPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_applepay/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_applepay/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/applepay.png');
    }

    /**
     * Get Paypal payment icon
     *
     * @return string|null
     */
    public function getPaypalPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_paypal/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_paypal/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/paypal.png');
    }

    /**
     * Get Klarna payment icon
     *
     * @return string|null
     */
    public function getKlarnaPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_klarna/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_klarna/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/klarna.png');
    }

    /**
     * Get Klarna Pay Now payment icon
     *
     * @return string|null
     */
    public function getKlarnapaynowPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_klarnapaynow/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_klarnapaynow/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/klarna-pay-now.png');
    }

    /**
     * Get Klarna Pay Later payment icon
     *
     * @return string|null
     */
    public function getKlarnapaylaterPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_klarnapaylater/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_klarnapaylater/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/klarna-pay-later.png');
    }

    /**
     * Get Klarna Slice It payment icon
     *
     * @return string|null
     */
    public function getKlarnaSliceItPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_klarnasliceit/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_klarnasliceit/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/klarna-slice-it.png');
    }

    /**
     * Get Klarna Direct Bank Transfer payment icon
     *
     * @return string|null
     */
    public function getKlarnaDirectBankTransferPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_klarnadirectbanktransfer/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_klarnadirectbanktransfer/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/klarna-direct-bank-transfer.png');
    }

    /**
     * Get Klarna Direct Debit payment icon
     *
     * @return string|null
     */
    public function getKlarnaDirectDebitPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_klarnadirectdebit/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_klarnadirectdebit/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/klarna-direct-debit.png');
    }

    /**
     * Get Swish payment icon
     *
     * @return string|null
     */
    public function getSwishPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_swish/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_swish/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/swish.png');
    }

    /**
     * Get Vipps payment icon
     *
     * @return string|null
     */
    public function getVippsPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_vipps/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_vipps/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/vipps.png');
    }

    /**
     * Get Forbrugsforeningen payment icon
     *
     * @return string|null
     */
    public function getForbrugsforeningenPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_forbrugsforeningen/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_forbrugsforeningen/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/forbrugsforeningen.png');
    }

    /**
     * Get Google pay payment icon
     *
     * @return string|null
     */
    public function getGooglepayPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_googlepay/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_googlepay/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/googlepay.png');
    }

    /**
     * Get iDEAL payment icon
     *
     * @return string|null
     */
    public function getIdealPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_ideal/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_ideal/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/ideal.png');
    }

    /**
     * Get BLIK payment icon
     *
     * @return string|null
     */
    public function getBlikPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_blik/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_blik/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/blik_oc.png');
    }

    /**
     * Get Przelewy24 (P24) payment icon
     *
     * @return string|null
     */
    public function getP24PaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_p24/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_p24/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/p24.png');
    }

    /**
     * Get Verkkopankki payment icon
     *
     * @return string|null
     */
    public function getVerkkopankkiPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_verkkopankki/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_verkkopankki/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/verkkopankki.png');
    }

    /**
     * Get giropay payment icon
     *
     * @return string|null
     */
    public function getGiropayPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_giropay/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_giropay/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/giropay.png');
    }

    /**
     * Get SEPA Direct Debit payment icon
     *
     * @return string|null
     */
    public function getSepaPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_sepa/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_sepa/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/sepa.png');
    }

    /**
     * Get Bancontact payment icon
     *
     * @return string|null
     */
    public function getBancontactPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_bancontact/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_bancontact/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/bancontact.png');
    }

    /**
     * Get Santander payment icon
     *
     * @return string|null
     */
    public function getSantanderPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_santander/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_santander/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/santander.png');
    }

    /**
     * Get EPS payment icon
     *
     * @return string|null
     */
    public function getEpsPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_eps/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_eps/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/eps.png');
    }

    /**
     * Get Estonia Banks payment icon
     *
     * @return string|null
     */
    public function getEstoniaBanksPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_estoniabanks/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_estoniabanks/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/estonia-banks.png');
    }

    /**
     * Get Latvia Banks payment icon
     *
     * @return string|null
     */
    public function getLatviaBanksPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_latviabanks/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_latviabanks/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/latvia-banks.png');
    }

    /**
     * Get Lithuania Banks payment icon
     *
     * @return string|null
     */
    public function getLithuaniaBanksPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_lithuaniabanks/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_lithuaniabanks/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/lithuania-banks.png');
    }

    /**
     * Get MB Way payment icon
     *
     * @return string|null
     */
    public function getMbWayPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_mbway/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_mbway/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/mb-way.png');
    }

    /**
     * Get Multibanco payment icon
     *
     * @return string|null
     */
    public function getMultibancoPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_multibanco/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_multibanco/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/multibanco.png');
    }

    /**
     * Get mBank payment icon
     *
     * @return string|null
     */
    public function getMybankPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_mybank/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_mybank/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/mybank.png');
    }

    /**
     * Get Payconiq payment icon
     *
     * @return string|null
     */
    public function getPayconiqPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_payconiq/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_payconiq/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/payconiq.png');
    }

    /**
     * Get Paysafecard payment icon
     *
     * @return string|null
     */
    public function getPaysafecardPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_paysafecard/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_paysafecard/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/paysafecard.png');
    }

    /**
     * Get Paysera payment icon
     *
     * @return string|null
     */
    public function getPayseraPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_paysera/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_paysera/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/paysera.png');
    }

    /**
     * Get PostFinance payment icon
     *
     * @return string|null
     */
    public function getPostfinancePaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_postfinance/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_postfinance/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/postfinance.png');
    }

    /**
     * Get Satisfy payment icon
     *
     * @return string|null
     */
    public function getSatispayPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_satispay/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_satispay/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/satispay.png');
    }

    /**
     * Get Trustly payment icon
     *
     * @return string|null
     */
    public function getTrustlyPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_trustly/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_trustly/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/trustly.png');
    }

    /**
     * Get WeChat Pay payment icon
     *
     * @return string|null
     */
    public function getWechatpayPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_wechatpay/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_wechatpay/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/wechatpay.png');
    }

    /**
     * Get Banktransfer payment icon
     *
     * @return string|null
     */
    public function getBanktransferPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_banktransfer/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_banktransfer/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/banktransfer.png');
    }

    /**
     * Get Cash payment icon
     *
     * @return string|null
     */
    public function getCashPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_cash/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_cash/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/cash.png');
    }

    /**
     * Get Other payment icon
     *
     * @return string|null
     */
    public function getOtherPaymentIcon()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $showIcon = $this->scopeConfig->getValue('payment/reepay_other/show_icon', $storeScope);
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->scopeConfig->getValue('payment/reepay_other/custom_icon', $storeScope);
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            // custom icon
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // default icon
        return $this->getViewFileUrl('Radarsofthouse_Reepay::img/payment_icons/other.png');
    }
}
