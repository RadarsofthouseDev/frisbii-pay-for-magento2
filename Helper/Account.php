<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Radarsofthouse\Reepay\Client\Api;

class Account extends AbstractHelper
{
    public const ENDPOINT = 'account';
    private const ERROR_INVALID_API_KEY = 'invalid_api_key';
    private const ERROR_CONNECTION = 'connection';
    private const ERROR_FRISBII = 'frisbii_error';
    private const CACHE_PREFIX = 'reepay_account_v2_';
    private const CACHE_LIFETIME = 2592000;

    /**
     * @var Api
     */
    private $client;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Context $context
     * @param CacheInterface $cache
     * @param Logger $logger
     */
    public function __construct(Context $context, CacheInterface $cache, Logger $logger)
    {
        parent::__construct($context);
        $this->client = new Api();
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Get account identity for the current API key.
     *
     * @param string $apiKey
     * @return array|false
     */
    public function getCurrentAccount($apiKey)
    {
        $result = $this->getCurrentAccountStatus($apiKey);

        return $result['account'];
    }

    /**
     * Get account identity and a safe error type for the admin label.
     *
     * @param string $apiKey
     * @return array
     */
    public function getCurrentAccountStatus($apiKey)
    {
        $apiKey = trim((string)$apiKey);
        if ($apiKey === '') {
            return [
                'account' => false,
                'error' => self::ERROR_INVALID_API_KEY,
            ];
        }

        $cacheKey = self::CACHE_PREFIX . hash('sha256', $apiKey);
        $cachedAccount = $this->cache->load($cacheKey);
        if ($cachedAccount !== false) {
            $cachedAccount = json_decode($cachedAccount, true);
            if (is_array($cachedAccount)) {
                return [
                    'account' => $cachedAccount,
                    'error' => null,
                ];
            }
        }

        try {
            $response = $this->client->get($apiKey, self::ENDPOINT);
            $account = $this->getSafeAccountData($response);
            if ($this->client->success() && $account !== false) {
                $this->cache->save(
                    json_encode($account),
                    $cacheKey,
                    [],
                    self::CACHE_LIFETIME
                );

                return [
                    'account' => $account,
                    'error' => null,
                ];
            }

            $errorType = $this->getErrorType();
            $logContext = [
                'http_errors' => $this->client->getHttpError(),
                'response_errors' => $this->client->getErrors(),
            ];
            if ($errorType === self::ERROR_INVALID_API_KEY) {
                $this->logger->addDebug(__METHOD__, $logContext, true);
            } else {
                $this->logger->addError(__METHOD__, $logContext, true);
            }

            return [
                'account' => false,
                'error' => $errorType,
            ];
        } catch (\Exception $exception) {
            $this->logger->addError(__METHOD__, [
                'exception_error' => $exception->getMessage(),
                'http_errors' => $this->client->getHttpError(),
                'response_errors' => $this->client->getErrors(),
            ], true);

            return [
                'account' => false,
                'error' => self::ERROR_CONNECTION,
            ];
        }
    }

    /**
     * Classify the API result without exposing response details to the browser.
     *
     * @return string
     */
    private function getErrorType()
    {
        $lastResponse = $this->client->getLastResponse();
        $headers = is_array($lastResponse) && isset($lastResponse['headers'])
            ? $lastResponse['headers'] : null;
        $httpCode = is_array($headers) && isset($headers['http_code'])
            ? (int)$headers['http_code'] : 0;

        if (in_array($httpCode, [400, 401, 403], true)) {
            return self::ERROR_INVALID_API_KEY;
        }

        if ($httpCode === 0) {
            return self::ERROR_CONNECTION;
        }

        return self::ERROR_FRISBII;
    }

    /**
     * Keep only the account fields needed by the admin label.
     *
     * @param mixed $response
     * @return array|false
     */
    private function getSafeAccountData($response)
    {
        if (!is_array($response) || empty($response['handle']) || empty($response['name'])) {
            return false;
        }

        return [
            'handle' => (string)$response['handle'],
            'name' => (string)$response['name'],
        ];
    }
}