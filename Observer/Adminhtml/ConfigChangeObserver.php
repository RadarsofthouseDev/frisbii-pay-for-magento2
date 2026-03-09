<?php

namespace Radarsofthouse\Reepay\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class ConfigChangeObserver implements ObserverInterface
{
    protected $_messageManager;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_configWriter;

    public function __construct(
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        WriterInterface $configWriter
    ) {
        $this->_messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_configWriter = $configWriter;
    }

    public function execute(Observer $observer)
    {
        // Get event data
        $website = $observer->getEvent()->getWebsite();
        $store = $observer->getEvent()->getStore();
        $changedPaths = $observer->getEvent()->getChangedPaths();

        // Get the current scope
        $scope = $store ? 'stores' : ($website ? 'websites' : 'default');
        $scopeId = $store ? $this->_storeManager->getStore($store)->getId() : ($website ? $this->_storeManager->getWebsite($website)->getId() : 0);

        $allowedPaymentPath = 'payment/reepay_payment/allowwed_payment';
        $mobilePayActivePath = 'payment/reepay_mobilepay/active';
        $allowedPayment = $this->_scopeConfig->getValue($allowedPaymentPath, $scope, $scopeId);
        $mobilePayActive = $this->_scopeConfig->getValue($mobilePayActivePath, $scope, $scopeId);
        if (strpos($allowedPayment, 'mobilepay') !== false || $mobilePayActive == '1') {
            $this->_messageManager->addWarningMessage(__('The new Vipps MobilePay payment method, which utilizes bank transfers instead of card payments, will replace the old MobilePay Online payment method. Please refer to Vipps MobilePay for more efficient transactions and a better conversion rate.'));
        }
    }
}
