<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH = 'payment/reepay_payment/';
    const REEPAY_PAYMENT_METHODS = [
        'reepay_payment',
        'reepay_viabill',
        'reepay_anyday',
        'reepay_mobilepay',
        'reepay_applepay',
        'reepay_paypal',
        'reepay_klarnapaynow',
        'reepay_klarnapaylater',
        'reepay_klarnasliceit',
        'reepay_klarnadirectbanktransfer',
        'reepay_klarnadirectdebit',
        'reepay_swish',
        'reepay_vipps',
        'reepay_forbrugsforeningen',
        'reepay_googlepay',
        'reepay_ideal',
        'reepay_blik',
        'reepay_p24',
        'reepay_verkkopankki',
        'reepay_giropay',
        'reepay_sepa',
        'reepay_bancontact',
        'reepay_santander',
        'reepay_eps',
        'reepay_estoniabanks',
        'reepay_latviabanks',
        'reepay_lithuaniabanks',
        'reepay_mbway',
        'reepay_multibanco',
        'reepay_mybank',
        'reepay_payconiq',
        'reepay_paysafecard',
        'reepay_paysera',
        'reepay_postfinance',
        'reepay_satispay',
        'reepay_trustly',
        'reepay_wechatpay',
        'reepay_banktransfer',
        'reepay_cash',
        'reepay_other',
        'reepay_vippsepayment'
    ];

    const REEPAY_AUTO_CAPTURE_METHODS = [
        'swish',
        'bancontact',
        'ideal',
        'sepa',
        'verkkopankki',
        'p24',
        'eps',
        'estonia_banks',
        'latvia_banks',
        'lithuania_banks',
        'mb_way',
        'multibanco',
        'mybank',
        'payconiq',
        'paysafecard',
        'paysera',
        'postfinance',
        'satispay',
        'wechatpay',
        'offline_cash',
        'offline_bank_transfer',
        'offline_other'
    ];

    const REEPAY_SOURCE_TYPE_AND_PAYMENT_METHOD_MAPPING = [
        'viabill' => 'reepay_viabill',
        'anyday' => 'reepay_anyday',
        'mpo' => 'reepay_mobilepay',
        'applepay' => 'reepay_applepay',
        'paypal' => 'reepay_paypal',
        'klarna_pay_now' => 'reepay_klarnapaynow',
        'klarna_pay_later' => 'reepay_klarnapaylater',
        'klarna_slice_it' => 'reepay_klarnasliceit',
        'klarna_direct_bank_transfer' => 'reepay_klarnadirectbanktransfer',
        'klarna_direct_debit' => 'reepay_klarnadirectdebit',
        'swish' => 'reepay_swish',
        'vipps' => 'reepay_vipps',
        'ffk' => 'reepay_forbrugsforeningen',
        'googlepay' => 'reepay_googlepay',
        'ideal' => 'reepay_ideal',
        'blik' => 'reepay_blik',
        'pp_blik_oc' => 'reepay_blik',
        'p24' => 'reepay_p24',
        'verkkopankki' => 'reepay_verkkopankki',
        'giropay' => 'reepay_giropay',
        'sepa' => 'reepay_sepa',
        'bancontact' => 'reepay_bancontact',
        'santander' => 'reepay_santander',
        'eps' => 'reepay_eps',
        'estonia_banks' => 'reepay_estoniabanks',
        'latvia_banks' => 'reepay_latviabanks',
        'lithuania_banks' => 'reepay_lithuaniabanks',
        'mb_way' => 'reepay_mbway',
        'multibanco' => 'reepay_multibanco',
        'mybank' => 'reepay_mybank',
        'payconiq' => 'reepay_payconiq',
        'paysafecard' => 'reepay_paysafecard',
        'paysera' => 'reepay_paysera',
        'postfinance' => 'reepay_postfinance',
        'satispay' => 'reepay_satispay',
        'trustly' => 'reepay_trustly',
        'wechatpay' => 'reepay_wechatpay',
        'offline_bank_transfer' => 'reepay_banktransfer',
        'offline_cash' => 'reepay_cash',
        'offline_other' => 'reepay_other',
        'vipps_epayment' => 'reepay_vippsepayment'
    ];


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_resolver;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Radarsofthouse\Reepay\Model\Status
     */
    protected $_reepayStatus;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $_transactionBuilder;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\Resolver $resolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Radarsofthouse\Reepay\Model\Status $reepayStatus
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Radarsofthouse\Reepay\Model\Status $reepayStatus,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_resolver = $resolver;
        $this->_scopeConfig = $scopeConfig;
        $this->_reepayStatus = $reepayStatus;
        $this->_transactionBuilder = $transactionBuilder;
        $this->_priceHelper = $priceHelper;
        $this->_productRepository = $productRepository;
    }

    /**
     * Get module configuration by field
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->_scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get modile configuration
     *
     * @param string $code
     * @param int $storeId
     * @return mixed
     */
    public function getConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_PATH . $code, $storeId);
    }

    /**
     * Get private api key from backend configuration
     *
     * @param integer $storeId
     * @return string $apiKey
     */
    public function getApiKey($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        $apiKey = null;
        $testModeConfig = $this->getConfig('api_key_type', $storeId);
        if ($testModeConfig == 1) {
            $apiKey = $this->getConfig('private_key', $storeId);
        } else {
            $apiKey = $this->getConfig('private_key_test', $storeId);
        }

        return $apiKey;
    }

    /**
     * Set reepay payment state to radarsofthouse_reepay_status
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param string $state
     * @return void
     */
    public function setReepayPaymentState($payment, $state)
    {
        $_additionalInfo = $payment->getAdditionalInformation();
        $_additionalInfo['raw_details_info']['state'] = $state;
        $payment->setAdditionalInformation($_additionalInfo);
        $payment->save();

        $orderId = $payment->getOrder()->getIncrementId();
        $reepayStatus = $this->_reepayStatus->load($orderId, 'order_id');
        if ($reepayStatus->getStatusId()) {
            $reepayStatus->setStatus($state);
            $reepayStatus->save();
        }
    }

    /**
     * Update reepay payment data to radarsofthouse_reepay_status
     *
     * @param string $orderId
     * @param array $data
     * @return void
     */
    public function updateReepayPaymentData($orderId, $data)
    {
        $reepayStatus = $this->_reepayStatus->load($orderId, 'order_id');
        if ($reepayStatus->getStatusId()) {
            if (isset($data['status']) && !empty($data['status'])) {
                $reepayStatus->setStatus($data['status']);
            }
            if (isset($data['first_name']) && !empty($data['first_name'])) {
                $reepayStatus->setFirstName($data['first_name']);
            }
            if (isset($data['last_name']) && !empty($data['last_name'])) {
                $reepayStatus->setLastName($data['last_name']);
            }
            if (isset($data['email']) && !empty($data['email'])) {
                $reepayStatus->setEmail($data['email']);
            }
            if (isset($data['token']) && !empty($data['token'])) {
                $reepayStatus->setToken($data['token']);
            }
            if (isset($data['masked_card_number']) && !empty($data['masked_card_number'])) {
                $reepayStatus->setMaskedCardNumber($data['masked_card_number']);
            }
            if (isset($data['fingerprint']) && !empty($data['fingerprint'])) {
                $reepayStatus->setFingerprint($data['fingerprint']);
            }
            if (isset($data['card_type']) && !empty($data['card_type'])) {
                $reepayStatus->setCardType($data['card_type']);
            }
            if (isset($data['error']) && !empty($data['error'])) {
                $reepayStatus->setError($data['error']);
            }
            if (isset($data['error_state']) && !empty($data['error_state'])) {
                $reepayStatus->setErrorState($data['error_state']);
            }

            $reepayStatus->save();
        }
    }

    /**
     * Get customer data from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array (customer data)
     */
    public function getCustomerData($order)
    {
        $testModeConfig = $this->getConfig('api_key_type', $order->getStoreId());
        $testMode = true;
        if ($testModeConfig == 1) {
            $testMode = false;
        }

        $address1 = $order->getBillingAddress()->getStreetLine(1);
        $address2 = $order->getBillingAddress()->getStreetLine(2);

        $vatId = '';
        if (!empty($order->getBillingAddress()->getVatId())) {
            $vatId = $order->getBillingAddress()->getVatId();
        }

        return [
            //            'handle' => $order->getBillingAddress()->getEmail(),
            'email' => $order->getBillingAddress()->getEmail(),
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name' => $order->getBillingAddress()->getLastname(),
            'address' => $address1,
            'address2' => $address2,
            'city' => $order->getBillingAddress()->getCity(),
            'country' => $order->getBillingAddress()->getCountryId(),
            'phone' => $order->getBillingAddress()->getTelephone(),
            'company' => $order->getBillingAddress()->getCompany(),
            'postal_code' => $order->getBillingAddress()->getPostcode(),
            'vat' => $vatId,
            'test' => $testMode,
            'generate_handle' => true,
        ];
    }

    /**
     * Get billing address from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array (billing address data)
     */
    public function getOrderBillingAddress($order)
    {
        if (null !== $order->getBillingAddress()) {
            $address1 = $order->getBillingAddress()->getStreetLine(1);
            $address2 = $order->getBillingAddress()->getStreetLine(2);

            $vatId = '';
            if (!empty($order->getBillingAddress()->getVatId())) {
                $vatId = $order->getBillingAddress()->getVatId();
            }

            $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();

            return [
                'company' => $paymentMethod == "reepay_santander" ? "" : $order->getBillingAddress()->getCompany(),
                'vat' => $vatId,
                'attention' => '',
                'address' => $address1,
                'address2' => $address2,
                'city' => $order->getBillingAddress()->getCity(),
                'country' => $order->getBillingAddress()->getCountryId(),
                'email' => $order->getBillingAddress()->getEmail(),
                'phone' => $order->getBillingAddress()->getTelephone(),
                'first_name' => $order->getBillingAddress()->getFirstname(),
                'last_name' => $order->getBillingAddress()->getLastname(),
                'postal_code' => $order->getBillingAddress()->getPostcode(),
                'state_or_province' => $order->getBillingAddress()->getRegion(),
            ];
        }
        return [
            'company' => '',
            'vat' => '',
            'attention' => '',
            'address' => '',
            'address2' => '',
            'city' => '',
            'country' => '',
            'email' => '',
            'phone' => '',
            'first_name' => '',
            'last_name' => '',
            'postal_code' => '',
            'state_or_province' => '',
        ];
    }

    /**
     * Get shipping address from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array (shipping address data)
     */
    public function getOrderShippingAddress($order)
    {
        if (null === $order->getShippingAddress()) {
            return $this->getOrderBillingAddress($order);
        }

        $address1 = $order->getShippingAddress()->getStreetLine(1);
        $address2 = $order->getShippingAddress()->getStreetLine(2);

        $vatId = '';
        if (!empty($order->getShippingAddress()->getVatId())) {
            $vatId = $order->getShippingAddress()->getVatId();
        }

        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();

        return [
            'company' => $paymentMethod == "reepay_santander" ? "" : $order->getShippingAddress()->getCompany(),
            'vat' => $vatId,
            'attention' => '',
            'address' => $address1,
            'address2' => $address2,
            'city' => $order->getShippingAddress()->getCity(),
            'country' => $order->getShippingAddress()->getCountryId(),
            'email' => $order->getShippingAddress()->getEmail(),
            'phone' => $order->getShippingAddress()->getTelephone(),
            'first_name' => $order->getShippingAddress()->getFirstname(),
            'last_name' => $order->getShippingAddress()->getLastname(),
            'postal_code' => $order->getShippingAddress()->getPostcode(),
            'state_or_province' => $order->getShippingAddress()->getRegion(),
        ];
    }

    /**
     * Get order lines data from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array $orderLines
     */
    public function getOrderLines($order)
    {
        $orderTotalDue = $order->getTotalDue() * 100;
        $orderTotalDue = $this->toInt($orderTotalDue);
        $total = 0;
        $orderLines = [];

        // products
        $orderitems = $order->getAllVisibleItems();
        foreach ($orderitems as $orderitem) {
            $amount = $orderitem->getPriceInclTax() * 100;
            $amount = round($amount);

            $qty = $orderitem->getQtyOrdered();

            $line = [
                'ordertext' => $orderitem->getProduct()->getName(),
                'amount' => $this->toInt($amount),
                'quantity' => $this->toInt($qty),
                'vat' => $orderitem->getTaxPercent() / 100,
                'amount_incl_vat' => "true",
            ];
            $orderLines[] = $line;

            $total = $total + $this->toInt($amount) * $this->toInt($qty);
        }

        /*
        // tax
        $taxAmount = ($order->getTaxAmount() * 100);
        if ($taxAmount != 0) {
            $line = [
                'ordertext' => __('Tax.'),
                'amount' => (int)$taxAmount,
                'quantity' => 1,
            ];
            $orderLines[] = $line;
            $total = $total + $taxAmount;
        }
        */

        // shipping
        $shippingAmount = ($order->getShippingInclTax() * 100);
        if ($shippingAmount != 0) {
            $line = [
                'ordertext' => !empty($order->getShippingDescription()) ? $order->getShippingDescription() :  __('Shipping')->render(),
                'amount' => $this->toInt($shippingAmount),
                'quantity' => 1,
            ];
            if ($order->getShippingTaxAmount() > 0) {
                $line['vat'] = $order->getShippingTaxAmount() / $order->getShippingAmount();
                $line['amount_incl_vat'] = "true";
            } else {
                $line['vat'] = 0;
                $line['amount_incl_vat'] = "true";
            }
            $orderLines[] = $line;
            $total = $total + $this->toInt($shippingAmount);
        }

        // discount
        $discountAmount = ($order->getDiscountAmount() * 100);
        if ($discountAmount != 0) {
            $line = [
                'ordertext' => !empty($order->getDiscountDescription()) ? __('Discount: %1', $order->getDiscountDescription())->render() :  __('Discount')->render(),
                'amount' => $this->toInt($discountAmount),
                'quantity' => 1,
                'vat' => 0,
                'amount_incl_vat' => "true",
            ];
            $orderLines[] = $line;
            $total = $total + $this->toInt($discountAmount);
        }

        // other
        if ($total != $orderTotalDue) {
            $amount = $orderTotalDue - $total;
            $line = [
                'ordertext' => __('Other')->render(),
                'amount' => $this->toInt($amount),
                'quantity' => 1,
                'vat' => 0,
                'amount_incl_vat' => "true",
            ];
            $orderLines[] = $line;
        }

        return $orderLines;
    }

    /**
     * Get order lines from invoice
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return array $orderLines
     */
    public function getOrderLinesFromInvoice($invoice)
    {
        $order = $invoice->getOrder();
        $invoiceTotal = $invoice->getGrandTotal() * 100;
        $invoiceTotal = $this->toInt($invoiceTotal);
        $total = 0;
        $orderLines = [];

        // products
        $invoiceItems = $invoice->getAllItems();
        foreach ($invoiceItems as $invoiceItem) {
            $amount = $invoiceItem->getPriceInclTax() * 100;
            $amount = round($amount);

            $qty = $invoiceItem->getQty();

            $line = [
                'ordertext' => $invoiceItem->getName(),
                'amount' => $this->toInt($amount),
                'quantity' => $this->toInt($qty),
                'vat' => $invoiceItem->getOrderItem()->getTaxPercent() / 100,
                'amount_incl_vat' => "true",
            ];
            $orderLines[] = $line;

            $total = $total + $this->toInt($amount) * $this->toInt($qty);
        }

        // shipping
        $shippingAmount = ($invoice->getShippingInclTax() * 100);
        if ($shippingAmount != 0) {
            $line = [
                'ordertext' => !empty($order->getShippingDescription()) ? $order->getShippingDescription() :  __('Shipping')->render(),
                'amount' => $this->toInt($shippingAmount),
                'quantity' => 1,
            ];
            if ($invoice->getShippingTaxAmount() > 0) {
                $line['vat'] = $invoice->getShippingTaxAmount() / $invoice->getShippingAmount();
                $line['amount_incl_vat'] = "true";
            } else {
                $line['vat'] = 0;
                $line['amount_incl_vat'] = "true";
            }
            $orderLines[] = $line;
            $total = $total + $this->toInt($shippingAmount);
        }

        // discount
        $discountAmount = ($invoice->getDiscountAmount() * 100);
        if ($discountAmount != 0) {
            $line = [
                'ordertext' => !empty($invoice->getDiscountDescription()) ? __('Discount: %1', $invoice->getDiscountDescription())->render() :  __('Discount')->render(),
                'amount' => $this->toInt($discountAmount),
                'quantity' => 1,
                'vat' => 0,
                'amount_incl_vat' => "true",
            ];
            $orderLines[] = $line;
            $total = $total + $this->toInt($discountAmount);
        }

        // other
        if ($total != $invoiceTotal) {
            $amount = $invoiceTotal - $total;
            $line = [
                'ordertext' => __('Other')->render(),
                'amount' => $this->toInt($amount),
                'quantity' => 1,
                'vat' => 0,
                'amount_incl_vat' => "true",
            ];
            $orderLines[] = $line;
        }

        return $orderLines;
    }

    /**
     * Convert variable to integer
     *
     * @param float|string $number
     * @return int
     */
    public function toInt($number)
    {
        if (gettype($number) == "double") {
            $number = round($number);
        }
        return (int)($number . "");
    }

    /**
     * Get allowwed payment from backend configuration
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array $paymentMethods
     */
    public function getPaymentMethods($order)
    {
        $paymentMethods = [];
        $orderPaymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        switch ($orderPaymentMethod) {
            case 'reepay_viabill':
                $paymentMethods[] = 'viabill';
                break;
            case 'reepay_anyday':
                $paymentMethods[] = 'anyday';
                break;
            case 'reepay_mobilepay':
                $paymentMethods[] = 'mobilepay';
                break;
            case 'reepay_applepay':
                $paymentMethods[] = 'applepay';
                break;
            case 'reepay_paypal':
                $paymentMethods[] = 'paypal';
                break;
            case 'reepay_klarnapaynow':
                $paymentMethods[] = 'klarna_pay_now';
                break;
            case 'reepay_klarnapaylater':
                $paymentMethods[] = 'klarna_pay_later';
                break;
            case 'reepay_klarnasliceit':
                $paymentMethods[] = 'klarna_slice_it';
                break;
            case 'reepay_klarnadirectbanktransfer':
                $paymentMethods[] = 'klarna_direct_bank_transfer';
                break;
            case 'reepay_klarnadirectdebit':
                $paymentMethods[] = 'klarna_direct_debit';
                break;
            case 'reepay_vipps':
                $paymentMethods[] = 'vipps';
                break;
            case 'reepay_forbrugsforeningen':
                $paymentMethods[] = 'ffk';
                break;
            case 'reepay_googlepay':
                $paymentMethods[] = 'googlepay';
                break;
            case 'reepay_ideal':
                $paymentMethods[] = 'ideal';
                break;
            case 'reepay_blik':
                $paymentMethods[] = 'blik_oc';
                break;
            case 'reepay_p24':
                $paymentMethods[] = 'p24';
                break;
            case 'reepay_verkkopankki':
                $paymentMethods[] = 'verkkopankki';
                break;
            case 'reepay_giropay':
                $paymentMethods[] = 'giropay';
                break;
            case 'reepay_sepa':
                $paymentMethods[] = 'sepa';
                break;
            case 'reepay_bancontact':
                $paymentMethods[] = 'bancontact';
                break;
            case 'reepay_santander':
                $paymentMethods[] = 'santander';
                break;
            case 'reepay_eps':
                $paymentMethods[] = 'eps';
                break;
            case 'reepay_estoniabanks':
                $paymentMethods[] = 'estonia_banks';
                break;
            case 'reepay_latviabanks':
                $paymentMethods[] = 'latvia_banks';
                break;
            case 'reepay_lithuaniabanks':
                $paymentMethods[] = 'lithuania_banks';
                break;
            case 'reepay_mbway':
                $paymentMethods[] = 'mb_way';
                break;
            case 'reepay_multibanco':
                $paymentMethods[] = 'multibanco';
                break;
            case 'reepay_mybank':
                $paymentMethods[] = 'mybank';
                break;
            case 'reepay_payconiq':
                $paymentMethods[] = 'payconiq';
                break;
            case 'reepay_paysafecard':
                $paymentMethods[] = 'paysafecard';
                break;
            case 'reepay_paysera':
                $paymentMethods[] = 'paysera';
                break;
            case 'reepay_postfinance':
                $paymentMethods[] = 'postfinance';
                break;
            case 'reepay_satispay':
                $paymentMethods[] = 'satispay';
                break;
            case 'reepay_trustly':
                $paymentMethods[] = 'trustly';
                break;
            case 'reepay_wechatpay':
                $paymentMethods[] = 'wechatpay';
                break;
            case 'reepay_banktransfer':
                $paymentMethods[] = 'bank_transfer';
                break;
            case 'reepay_cash':
                $paymentMethods[] = 'cash';
                break;
            case 'reepay_other':
                $paymentMethods[] = 'other';
                break;
            case 'reepay_vippsepayment':
                $paymentMethods[] = 'vipps_epayment';
                break;
            case 'reepay_swish':
                $paymentMethods[] = 'swish';
                break;
            default:
                $allowedPaymentConfig = $this->getConfig('allowwed_payment', $order->getStoreId());
                $paymentMethods = explode(',', $allowedPaymentConfig);
                $currencyCode = $order->getOrderCurrency()->getCurrencyCode();
                if (!in_array($currencyCode, ['DKK', 'EUR', 'NOK'])) {
                    $key = array_search('vipps_epayment', $paymentMethods);
                    if ($key !== false) {
                        unset($paymentMethods[$key]);
                        $paymentMethods = array_values($paymentMethods);
                    }
                }
        }

        return $paymentMethods;
    }

    /**
     * Prepare payment data
     *
     * @param array $paymentData
     * @return array $paymentData
     */
    public function preparePaymentData($paymentData)
    {
        if (isset($paymentData['order_lines'])) {
            unset($paymentData['order_lines']);
        }

        if (isset($paymentData['billing_address'])) {
            unset($paymentData['billing_address']);
        }

        if (isset($paymentData['shipping_address'])) {
            unset($paymentData['shipping_address']);
        }

        if (isset($paymentData['source'])) {
            $source = $paymentData['source'];
            unset($paymentData['source']);

            foreach ($source as $key => $value) {
                $paymentData['source_' . $key] = $value;
            }
        }

        if (isset($paymentData['amount']) && $paymentData['amount'] > 0) {
            $paymentData['amount'] = $paymentData['amount'] / 100;
        }

        if (isset($paymentData['refunded_amount']) && $paymentData['refunded_amount'] > 0) {
            $paymentData['refunded_amount'] = $paymentData['refunded_amount'] / 100;
        }

        if (isset($paymentData['authorized_amount']) && $paymentData['authorized_amount'] > 0) {
            $paymentData['authorized_amount'] = $paymentData['authorized_amount'] / 100;
        }

        return $paymentData;
    }

    /**
     * Add transaction to order
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $paymentData
     * @return string Transaction Id
     */
    public function addTransactionToOrder($order, $paymentData = [])
    {
        try {
            $paymentData = $this->preparePaymentData($paymentData);

            $state = '';
            $isClosed = 0;
            if ($paymentData['state'] == 'authorized') {
                $state = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                $isClosed = 0;
            } elseif ($paymentData['state'] == 'settled') {
                $state = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                $isClosed = 1;
            }

            $payment = $order->getPayment();
            $payment->setLastTransId($paymentData['transaction']);
            $payment->setTransactionId($paymentData['transaction']);
            $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]);

            $formatedPrice = $order->formatPrice($order->getGrandTotal());

            $transaction = $this->_transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['transaction'])
                ->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData])
                ->setFailSafe(true)
                ->build($state)
                ->setIsClosed($isClosed);

            // Add transaction to payment
            $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
            $payment->setParentTransactionId(null);

            // Save payment, transaction and order
            $payment->save();
            $order->save();
            $transaction->save();

            $orderStatusAfterPayment = $this->getConfig('order_status_after_payment', $order->getStoreId());
            if (!empty($orderStatusAfterPayment)) {
                $totalDue = $order->formatPrice($order->getTotalDue());
                $order->setState($orderStatusAfterPayment, true);
                $order->setStatus($orderStatusAfterPayment);
                $order->addStatusToHistory($order->getStatus(), 'Frisbii : The authorized amount is ' . $totalDue);
                $order->save();
            }

            return  $transaction->getTransactionId();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\PaymentException(__('addTransactionToOrder() Exception : ' . $e->getMessage()));
            return;
        }
    }

    /**
     * Prepare capture transaction data
     *
     * @param array $transactionData
     * @return array $transactionData
     */
    public function prepareCaptureTransactionData($transactionData)
    {
        if (isset($transactionData['amount'])) {
            $transactionData['amount'] = $this->convertAmount($transactionData['amount']);
        }

        if (isset($transactionData['card_transaction'])) {
            $cardTransaction = $transactionData['card_transaction'];
            unset($transactionData['card_transaction']);
            $transactionData['card_transaction_ref_transaction'] = array_key_exists('ref_transaction', $cardTransaction) ? $cardTransaction['ref_transaction'] : '';
            $transactionData['card_transaction_fingerprint'] = array_key_exists('fingerprint', $cardTransaction) ? $cardTransaction['fingerprint'] : '';
            $transactionData['card_transaction_card_type'] = array_key_exists('card_type', $cardTransaction) ? $cardTransaction['card_type'] : '';
            $transactionData['card_transaction_exp_date'] = array_key_exists('exp_date', $cardTransaction) ? $cardTransaction['exp_date'] : '';
            $transactionData['card_transaction_masked_card'] = array_key_exists('masked_card', $cardTransaction) ? $cardTransaction['masked_card'] : '';
        }

        return $transactionData;
    }

    /**
     * Create capture transaction
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $transactionData
     * @param array $chargeRes
     * @return int (Magento Transaction ID)
     */
    public function addCaptureTransactionToOrder($order, $transactionData = [], $chargeRes = [], $authorizationTxnId = null)
    {
        try {
            // prepare transaction data
            $transactionData = $this->prepareCaptureTransactionData($transactionData);

            // prepare payment data from Charge
            $paymentData = $this->preparePaymentData($chargeRes);

            //get payment object from order object
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionData['id']);
            $payment->setTransactionId($transactionData['id']);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]
            );
            $payment->setParentTransactionId($authorizationTxnId);

            $formatedPrice = $order->formatPrice($transactionData['amount']);
            $message = __('Frisbii : Captured amount of %1 by the webhook.', $formatedPrice);

            $transaction = $this->_transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionData['id'])
                ->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $transactionData]
                )
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->save();
            $order->save();

            $transactionId = $transaction->save()->getTransactionId();

            $orderStatusAfterPayment = $this->getConfig('order_status_after_payment', $order->getStoreId());
            $autoCapture = $this->getConfig('auto_capture', $order->getStoreId());

            $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
            $reepayMethod = isset($chargeRes['source']['type']) ? $chargeRes['source']['type'] : '';
            if (($this->isReepayPaymentMethod($paymentMethod) && $order->getPayment()->getMethodInstance()->isAutoCapture()) ||
                $this->isReepayMethodAutoCapture($paymentMethod, $reepayMethod)
            ) {
                $autoCapture = 1;
            }

            if (!empty($orderStatusAfterPayment) && $autoCapture) {
                $totalDue = $order->formatPrice($order->getTotalDue());
                $order->setState($orderStatusAfterPayment, true);
                $order->setStatus($orderStatusAfterPayment);
                $order->save();
            }

            return  $transactionId;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\PaymentException(__('addCaptureTransactionToOrder() Exception : ' . $e->getMessage()));

            return;
        }
    }

    /**
     * Prepare refund transaction data
     *
     * @param array $transactionData
     * @return array $transactionData
     */
    public function prepareRefundTransactionData($transactionData)
    {
        if (isset($transactionData['amount'])) {
            $transactionData['amount'] = $this->convertAmount($transactionData['amount']);
        }

        if (isset($transactionData['card_transaction'])) {
            $cardTransaction = $transactionData['card_transaction'];
            unset($transactionData['card_transaction']);
            $transactionData['card_transaction_ref_transaction'] = $cardTransaction['ref_transaction'];
        }

        return $transactionData;
    }

    /**
     * Create refund transaction
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $transactionData
     * @param array $chargeRes
     * @return int (Magento Transaction ID)
     */
    public function addRefundTransactionToOrder($order, $transactionData = [], $chargeRes = [])
    {
        try {
            // prepare transaction data
            $transactionData = $this->prepareRefundTransactionData($transactionData);

            // prepare payment data from Charge
            $paymentData = $this->preparePaymentData($chargeRes);

            //get payment object from order object
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionData['id']);
            $payment->setTransactionId($transactionData['id']);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]
            );

            $formatedPrice = $order->formatPrice($transactionData['amount']);
            $message = __('Frisbii : Refunded amount of %1 by the webhook.', $formatedPrice);

            $transaction = $this->_transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionData['id'])
                ->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $transactionData]
                )
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();

            return  $transaction->save()->getTransactionId();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\PaymentException(__('addRefundTransactionToOrder() Exception : ' . $e->getMessage()));

            return;
        }
    }

    /**
     * Convert integer amount to 2 decimal places
     *
     * @param int $amount
     * @return float
     */
    public function convertAmount($amount)
    {
        return number_format((float)($amount / 100), 2, '.', '');
    }

    /**
     * Get SurchargeFee Enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isSurchargeFeeEnabled($storeId = null)
    {
        return $this->getConfig('surcharge_fee', $storeId) == 1;
    }

    /**
     * Check is Reepay payment method
     *
     * @param string $method
     * @return bool
     */
    public function isReepayPaymentMethod($method = '')
    {
        if (in_array($method, self::REEPAY_PAYMENT_METHODS, true)) {
            return true;
        }
        return false;
    }

    public function isReepayMethodAutoCapture($method = '', $reepayMethod = '')
    {
        if ($method === 'reepay_payment' && in_array($reepayMethod, self::REEPAY_AUTO_CAPTURE_METHODS, true)) {
            return true;
        }
        return false;
    }

    /**
     * Check if order requires age verification
     *
     * @param Order $order
     * @return bool|int
     */
    public function getAgeVerification($order)
    {
        $storeId = $order->getStoreId();
        $ageVerificationEnabled = $this->getConfig('age_verification_enabled', $storeId);
        if ($ageVerificationEnabled) {
            $ageVerificationNumber = 0;
            foreach ($order->getAllItems() as $item) {
                try {
                    $product = $this->_productRepository->getById($item->getProductId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }
                $isEnabled = $product->getData('frisbii_age_verification_enabled');
                if ($isEnabled) {
                    $attribute = $product->getCustomAttribute('frisbii_minimum_user_age');
                    if ($attribute && $attribute->getValue() > $ageVerificationNumber) {
                        $ageVerificationNumber = (int)$attribute->getValue();
                    }
                } elseif ($item->getParentItem()) {
                    try {
                        $product = $this->_productRepository->getById($item->getParentItem()->getProductId());
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }
                    $isEnabled = $product->getData('frisbii_age_verification_enabled');
                    if ($isEnabled) {
                        $attribute = $product->getCustomAttribute('frisbii_minimum_user_age');
                        if ($attribute && $attribute->getValue() > $ageVerificationNumber) {
                            $ageVerificationNumber = (int)$attribute->getValue();
                        }
                    }
                }
            }
            return $ageVerificationNumber > 0 ? $ageVerificationNumber : false;
        }
        return false;
    }
}
