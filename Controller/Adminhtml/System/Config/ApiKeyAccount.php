<?php

namespace Radarsofthouse\Reepay\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Radarsofthouse\Reepay\Helper\Account as AccountHelper;
use Radarsofthouse\Reepay\Helper\Data as DataHelper;

class ApiKeyAccount extends Action
{
    public const ADMIN_RESOURCE = 'Radarsofthouse_Reepay::config';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var AccountHelper
     */
    private $accountHelper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $dataHelper
     * @param AccountHelper $accountHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        DataHelper $dataHelper,
        AccountHelper $accountHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->accountHelper = $accountHelper;
    }

    /**
     * Verify configured Test and Live API keys and return only account identity.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $configuredKeys = [];

        foreach ($this->storeManager->getStores() as $store) {
            $storeId = (int)$store->getId();
            foreach (['private_key_test' => 'test', 'private_key' => 'live'] as $keyType => $environment) {
                $apiKey = $this->dataHelper->getConfiguredApiKey($keyType, $storeId);
                if ($apiKey === '') {
                    continue;
                }

                $fingerprint = hash('sha256', $apiKey);
                if (!isset($configuredKeys[$fingerprint])) {
                    $configuredKeys[$fingerprint] = [
                        'api_key' => $apiKey,
                        'references' => [],
                    ];
                }

                $configuredKeys[$fingerprint]['references'][] = [
                    'environment' => $environment,
                    'store_code' => (string)$store->getCode(),
                ];
            }
        }

        $statuses = [];
        foreach ($configuredKeys as $configuredKey) {
            $accountStatus = $this->accountHelper->getCurrentAccountStatus($configuredKey['api_key']);
            foreach ($configuredKey['references'] as $reference) {
                $statuses[] = array_merge($reference, [
                    'account' => $accountStatus['account'],
                    'error' => $accountStatus['error'],
                ]);
            }
        }

        return $result->setData([
            'success' => true,
            'statuses' => $statuses,
        ]);
    }
}