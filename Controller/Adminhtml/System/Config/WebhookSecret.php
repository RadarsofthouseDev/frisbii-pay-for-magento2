<?php

namespace Radarsofthouse\Reepay\Controller\Adminhtml\System\Config;

class WebhookSecret extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Radarsofthouse_Reepay::config';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Webhook
     */
    private $webhookHelper;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    private $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Radarsofthouse\Reepay\Helper\Webhook $webhookHelper
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Radarsofthouse\Reepay\Helper\Webhook $webhookHelper,
        \Radarsofthouse\Reepay\Helper\Logger $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        $this->cacheTypeList = $cacheTypeList;
        $this->encryptor = $encryptor;
        $this->timezone = $timezone;
        $this->webhookHelper = $webhookHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $lastUpdateTime = $this->timezone->formatDate(null, \IntlDateFormatter::MEDIUM, true);

        if (!$this->getRequest()->isPost()) {
            return $result->setHttpResponseCode(405)
                ->setHeader('Allow', 'POST', true)
                ->setData([
                    'success' => false,
                    'message' => 'Only POST requests are allowed.',
                    'time' => $lastUpdateTime,
                ]);
        }

        try {
            $apiKeyData = $this->getApiKeyData();
            $synced = [];
            $failed = [];
            $skipped = $apiKeyData['skipped'];
            $removed = [];
            $cleanupFailed = false;
            $accounts = [];

            foreach ($apiKeyData['entries'] as $entry) {
                $fingerprint = hash('sha256', $entry['api_key']);
                if (!isset($accounts[$fingerprint])) {
                    $accounts[$fingerprint] = [
                        'api_key' => $entry['api_key'],
                        'entries' => [],
                    ];
                }
                $accounts[$fingerprint]['entries'][] = $entry;
            }

            $connection = $this->resourceConnection->getConnection();
            $configTable = $this->resourceConnection->getTableName('core_config_data');

            foreach ($accounts as $account) {
                $settings = $this->webhookHelper->getSettings($account['api_key']);
                $secret = is_array($settings) && isset($settings['secret']) && is_scalar($settings['secret'])
                    ? trim((string)$settings['secret'])
                    : '';

                if ($secret === '') {
                    foreach ($account['entries'] as $entry) {
                        $failed[] = array_merge(
                            $this->getReference($entry),
                            ['reason' => 'Webhook secret could not be retrieved.']
                        );
                    }
                    continue;
                }

                try {
                    $accountSynced = [];
                    $connection->beginTransaction();
                    foreach ($account['entries'] as $entry) {
                        $this->saveSecret($connection, $configTable, $entry, $secret);
                        $accountSynced[] = $this->getReference($entry);
                    }
                    $connection->commit();
                    $synced = array_merge($synced, $accountSynced);
                } catch (\Exception $exception) {
                    $connection->rollBack();
                    foreach ($account['entries'] as $entry) {
                        $failed[] = array_merge(
                            $this->getReference($entry),
                            ['reason' => 'Webhook secret could not be saved.']
                        );
                    }
                    $this->logger->addError('Failed to save synchronized webhook secret.', [
                        'scope_count' => count($account['entries']),
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            try {
                $connection->beginTransaction();
                $removed = $this->removeOrphanedSecrets(
                    $connection,
                    $configTable,
                    $apiKeyData['direct_keys']
                );
                $connection->commit();
            } catch (\Exception $exception) {
                $connection->rollBack();
                $cleanupFailed = true;
                $this->logger->addError('Failed to remove orphaned webhook secrets.', [
                    'error' => $exception->getMessage(),
                ]);
            }

            if (!empty($synced) || !empty($removed)) {
                $this->cacheTypeList->cleanType('config');
            }

            $success = !$cleanupFailed
                && empty($failed)
                && (empty($skipped) || !empty($synced));
            if ($cleanupFailed) {
                $message = 'Some orphaned webhook secrets could not be removed.';
            } elseif (empty($apiKeyData['entries']) && empty($skipped)) {
                $message = empty($removed)
                    ? 'No directly configured API keys were found.'
                    : 'Orphaned webhook secrets removed. No directly configured API keys were found.';
            } elseif (empty($apiKeyData['entries'])) {
                $message = 'No usable directly configured API keys were found.';
            } elseif ($success && !empty($skipped)) {
                $message = 'Webhook secrets synchronized. Some API keys were skipped.';
            } else {
                $message = $success
                    ? 'Webhook secrets synchronized.'
                    : 'Some webhook secrets could not be synchronized.';
            }

            $this->logger->addInfo('Synchronized webhook secrets.', [
                'synced' => $synced,
                'failed' => $failed,
                'skipped' => $skipped,
                'removed' => $removed,
            ]);

            return $result->setData([
                'success' => $success,
                'message' => $message,
                'synced' => $synced,
                'failed' => $failed,
                'skipped' => $skipped,
                'removed' => $removed,
                'time' => $lastUpdateTime,
            ]);
        } catch (\Exception $exception) {
            $this->logger->addError('Failed to synchronize webhook secrets.', [
                'error' => $exception->getMessage(),
            ]);

            return $result->setData([
                'success' => false,
                'message' => 'Webhook secret synchronization failed.',
                'time' => $lastUpdateTime,
            ]);
        }
    }

    /**
     * Read only API keys explicitly stored in core_config_data.
     *
     * @return array
     */
    private function getApiKeyData()
    {
        $keyPaths = [
            'payment/reepay_payment/private_key' => [
                'key_type' => 'private_key',
                'secret_path' => 'payment/reepay_payment/webhook_secret',
            ],
            'payment/reepay_payment/private_key_test' => [
                'key_type' => 'private_key_test',
                'secret_path' => 'payment/reepay_payment/webhook_secret_test',
            ],
        ];
        $connection = $this->resourceConnection->getConnection();
        $configTable = $this->resourceConnection->getTableName('core_config_data');
        $select = $connection->select()
            ->from($configTable, ['scope', 'scope_id', 'path', 'value'])
            ->where('path IN (?)', array_keys($keyPaths));

        $entries = [];
        $skipped = [];
        $directKeys = [];
        foreach ($connection->fetchAll($select) as $config) {
            $keyPath = (string)$config['path'];
            $keyInfo = $keyPaths[$keyPath];
            $entry = [
                'scope' => (string)$config['scope'],
                'scope_id' => (int)$config['scope_id'],
                'key_type' => $keyInfo['key_type'],
                'secret_path' => $keyInfo['secret_path'],
            ];
            $directKeys[$this->getConfigKey(
                $entry['scope'],
                $entry['scope_id'],
                $entry['secret_path']
            )] = true;
            $apiKey = $this->decryptConfigValue($config['value']);
            if ($apiKey === '') {
                $skipped[] = array_merge(
                    $this->getReference($entry),
                    ['reason' => 'API key is empty or could not be decrypted.']
                );
                continue;
            }

            $entry['api_key'] = $apiKey;
            $entries[] = $entry;
        }

        return [
            'entries' => $entries,
            'skipped' => $skipped,
            'direct_keys' => $directKeys,
        ];
    }

    /**
     * Remove secret rows whose matching API key is no longer configured directly.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $configTable
     * @param array $directKeys
     * @return array
     */
    private function removeOrphanedSecrets($connection, $configTable, array $directKeys)
    {
        $secretPaths = [
            'payment/reepay_payment/webhook_secret' => 'private_key',
            'payment/reepay_payment/webhook_secret_test' => 'private_key_test',
        ];
        $select = $connection->select()
            ->from($configTable, ['config_id', 'scope', 'scope_id', 'path'])
            ->where('path IN (?)', array_keys($secretPaths));

        $removed = [];
        foreach ($connection->fetchAll($select) as $config) {
            $scope = (string)$config['scope'];
            $scopeId = (int)$config['scope_id'];
            $path = (string)$config['path'];
            if (isset($directKeys[$this->getConfigKey($scope, $scopeId, $path)])) {
                continue;
            }

            $connection->delete($configTable, ['config_id = ?' => (int)$config['config_id']]);
            $removed[] = [
                'scope' => $scope,
                'scope_id' => $scopeId,
                'key_type' => $secretPaths[$path],
            ];
        }

        return $removed;
    }

    /**
     * Decrypt a stored API key without exposing it in the result or logs.
     *
     * @param mixed $value
     * @return string
     */
    private function decryptConfigValue($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d+:\d+:/', $value)) {
            try {
                return trim((string)$this->encryptor->decrypt($value));
            } catch (\Exception $exception) {
                return '';
            }
        }

        return $value;
    }

    /**
     * Save an encrypted secret at the same scope as its API key.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $configTable
     * @param array $entry
     * @param string $secret
     * @return void
     */
    private function saveSecret($connection, $configTable, array $entry, $secret)
    {
        $encryptedSecret = $this->encryptor->encrypt($secret);
        if (!is_string($encryptedSecret) || $encryptedSecret === '') {
            throw new \RuntimeException('Webhook secret encryption failed.');
        }

        $select = $connection->select()
            ->from($configTable, ['config_id'])
            ->where('scope = ?', $entry['scope'])
            ->where('scope_id = ?', $entry['scope_id'])
            ->where('path = ?', $entry['secret_path']);
        $configId = $connection->fetchOne($select);
        if ($configId) {
            $connection->update(
                $configTable,
                ['value' => $encryptedSecret],
                ['config_id = ?' => (int)$configId]
            );
            return;
        }

        $connection->insert($configTable, [
            'scope' => $entry['scope'],
            'scope_id' => $entry['scope_id'],
            'path' => $entry['secret_path'],
            'value' => $encryptedSecret,
        ]);
    }

    /**
     * Return a safe scope reference for admin results and logs.
     *
     * @param array $entry
     * @return array
     */
    private function getReference(array $entry)
    {
        return [
            'scope' => $entry['scope'],
            'scope_id' => $entry['scope_id'],
            'key_type' => $entry['key_type'],
        ];
    }

    /**
     * Build a stable scope/path key for matching config rows.
     *
     * @param string $scope
     * @param int $scopeId
     * @param string $path
     * @return string
     */
    private function getConfigKey($scope, $scopeId, $path)
    {
        return $scope . ':' . (int)$scopeId . ':' . $path;
    }
}
