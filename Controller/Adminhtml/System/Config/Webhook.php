<?php

namespace Radarsofthouse\Reepay\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Radarsofthouse\Reepay\Helper\Logger;
use Radarsofthouse\Reepay\Helper\Webhook as WebhookHelper;
use Radarsofthouse\Reepay\Helper\Data as DataHelper;

class Webhook extends Action
{
    const ADMIN_RESOURCE = 'Radarsofthouse_Reepay::config';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var WebhookHelper
     */
    private $webhookHelper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $helper
     * @param WebhookHelper $webhookHelper
     * @param TimezoneInterface $timezone
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        DataHelper $helper,
        WebhookHelper $webhookHelper,
        TimezoneInterface $timezone,
        Logger $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->webhookHelper = $webhookHelper;
        $this->timezone = $timezone;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $lastUpdateTime = $this->timezone->formatDate(null, \IntlDateFormatter::MEDIUM, true);

        try {
            $apiKeys = [];
            $seenApiKeys = [];
            $stores = $this->storeManager->getStores();
            foreach ($stores as $store) {
                $storeId = $store->getId();
                foreach (['private_key', 'private_key_test'] as $keyType) {
                    $apiKey = $this->helper->getConfiguredApiKey($keyType, $storeId);
                    $apiKeyFingerprint = $apiKey !== '' ? hash('sha256', $apiKey) : '';
                    if ($apiKeyFingerprint !== '' && !isset($seenApiKeys[$apiKeyFingerprint])) {
                        $seenApiKeys[$apiKeyFingerprint] = true;
                        $apiKeys[] = [
                            'store_id' => $storeId,
                            'key_type' => $keyType,
                            'api_key' => $apiKey,
                        ];
                    }
                }
            }

            $failure = [];
            $success = [];
            foreach ($apiKeys as $apiKeyData) {
                $updatedUrls = $this->webhookHelper->updateUrl($apiKeyData['api_key']);
                $keyReference = [
                    'store_id' => $apiKeyData['store_id'],
                    'key_type' => $apiKeyData['key_type'],
                ];
                if ($updatedUrls === false) {
                    $failure[] = array_merge($keyReference, [
                        'error' => $this->webhookHelper->getLastError(),
                    ]);
                } else {
                    $success[] = array_merge($keyReference, ['urls' => $updatedUrls]);
                }
            }

            if (!empty($failure)) {
                $this->logger->addError('Failed to update webhook URLs', ['failed_keys' => $failure], true);
                return $result->setData([
                    'success' => false,
                    'message' => 'Unable to update webhook URLs for one or more API keys.',
                    'failed_keys' => $failure,
                    'time' => $lastUpdateTime
                ]);
            }

            $this->logger->addInfo('Updated webhook URLs', ['success' => $success]);
            return $result->setData([
                'success' => true,
                'updated_urls' => $success,
                'time' => $lastUpdateTime
            ]);
        } catch (\Exception $e) {
            $this->logger->addError('Unexpected error while updating webhook URLs.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $result->setData([
            'success' => false,
            'message' => 'Unexpected error while updating webhook URLs. Check the server log.',
            'time' => $lastUpdateTime,
        ]);
    }
}
