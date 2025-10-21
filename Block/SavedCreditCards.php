<?php

namespace Radarsofthouse\Reepay\Block;

class SavedCreditCards extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Customer
     */
    protected $_customerHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $_reepayHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Radarsofthouse\Reepay\Model\Config\Source\Allowwedpayment
     */
    protected $_allowwedpayment;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Radarsofthouse\Reepay\Helper\Customer $customerHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Radarsofthouse\Reepay\Model\Config\Source\Allowwedpayment $allowwedpayment
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Radarsofthouse\Reepay\Helper\Customer $customerHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Radarsofthouse\Reepay\Model\Config\Source\Allowwedpayment $allowwedpayment,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
        $this->_customerHelper = $customerHelper;
        $this->_customerSession = $customerSession;
        $this->_reepayHelper = $reepayHelper;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
        $this->_urlInterface = $urlInterface;
        $this->_checkoutSession = $checkoutSession;
        $this->_allowwedpayment = $allowwedpayment;
    }

    /**
     * Get saved credit card
     *
     * @return array $SavedCreditCards
     */
    public function getSavedCreditCards()
    {
        $savedCreditCards = [];

        $storeId = $this->_storeManager->getStore()->getId();
        $save_card_enable = $this->_reepayHelper->getConfig(
            'save_card_enable',
            $storeId
        );
        if ($save_card_enable) {
            if ($this->_customerSession->isLoggedIn()) {
                $apiKey = $this->_reepayHelper->getApiKey($this->_storeManager->getStore()->getId());
                $savedCreditCards = $this->_customerHelper->getPaymentCardsByCustomer(
                    $apiKey,
                    $this->_customerSession->getCustomer()
                );
            }
        }

        // It can be overiidden by Age Verification
        $ageVerificationEnabled = (bool)$this->_reepayHelper->getConfig(
            'age_verification_enabled',
            $storeId
        );

        if (!empty($savedCreditCards)) {
            $hasAgeRestrictedProduct = false;
            $quote = $this->_checkoutSession->getQuote();

            foreach ($quote->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $ageAttr = $product->getCustomAttribute('frisbii_minimum_user_age');
                $minimumAge = $ageAttr ? (int)$ageAttr->getValue() : 0;

                if ($minimumAge > 0) {
                    $hasAgeRestrictedProduct = true;
                    break;
                }
            }

            // disabled save card on checkout page when Aage Verification is enabled and has Age Restricted Product in cart
            if ($ageVerificationEnabled && $hasAgeRestrictedProduct) {
                $savedCreditCards = [];
            }
        }

        return $savedCreditCards;
    }

    /**
     * Get saved credit card from quote
     *
     * @return array $SavedCreditCards
     */
    public function getSavedCreditCardFromQuote()
    {
        $quote = $this->_checkoutSession->getQuote();
        return $quote->getReepayCreditCard();
    }

    /**
     * Get Allowwedpayments
     *
     * @return array $_allowwedpayments
     */
    public function getAllowwedpayments()
    {
        $allowwedpayments = $this->_allowwedpayment->toOptionArray();
        $_allowwedpayments = [];
        foreach ($allowwedpayments as $allowwedpayment) {
            if ($allowwedpayment['value'] == 'card') {
                $_allowwedpayments[$allowwedpayment['value']] = __('Debit/Credit card');
            } else {
                $_allowwedpayments[$allowwedpayment['value']] = $allowwedpayment['label'];
            }
        }
        return $_allowwedpayments;
    }

    /**
     * Get Remove Card URL
     *
     * @return string
     */
    public function getRemoveCardUrl()
    {
        return $this->_urlInterface->getUrl('reepay/standard/removeCard');
    }

    /**
     * Get Set Credit Card URL
     *
     * @return string
     */
    public function getSetCreditCardUrl()
    {
        return $this->_urlInterface->getUrl('reepay/standard/setCreditCard');
    }
}
