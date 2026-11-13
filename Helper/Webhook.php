<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Radarsofthouse\Reepay\Client\Api;

class Webhook extends AbstractHelper
{
    public const ENDPOINT = 'account/webhook_settings';
    const WEBHOOK_PATH = '/reepay/webhooks/index';

    /**
     * @var \Radarsofthouse\Reepay\Client\Api
     */
    private $client = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    private $logger = null;

    /**
     * @var array
     */
    private $lastError = [];

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager, Logger $logger)
    {
        parent::__construct($context);
        $this->client = new Api();
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Get webhook settings from Frisbii.
     *
     * @param string $apiKey
     * @return array|false
     */
    public function getSettings($apiKey)
    {
        $this->lastError = [];
        $param = [];
        $log = ['param' => $param];
        try {
            $response = $this->client->get($apiKey, self::ENDPOINT, $param);
            $log['response'] = $this->getSafeSettingsLog($response);
            $this->logger->addInfo(__METHOD__, $log, true);
            if ($this->client->success()) {
                if (is_array($response)) {
                    return $response;
                }

                $this->lastError = ['message' => 'Invalid response from webhook settings endpoint.'];
            } else {
                $this->lastError = $this->getClientError();
            }
        } catch (\Exception $e) {
            $log['exception_error'] = $e->getMessage();
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addInfo(__METHOD__, $log, true);
            $this->lastError = $this->getClientError();
        }
        return false;
    }

    /**
     * Return safe details about the last failed webhook API request.
     *
     * @return array
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Get configured webhook URLs from Frisbii.
     *
     * @param string $apiKey
     * @return array|false
     */
    public function getUrl($apiKey)
    {
        $settings = $this->getSettings($apiKey);

        return is_array($settings) && isset($settings['urls']) ? $settings['urls'] : false;
    }

    /**
     * Update webhook url
     *
     * @param string $apiKey
     * @return bool
     */
    public function updateUrl($apiKey)
    {
        $this->lastError = [];
        // Try to get the default store view first
        $defaultStore = $this->storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            // Default store not found, get the first activated store
            $stores = $this->storeManager->getStores();
            foreach ($stores as $store) {
                if ($store->getIsActive()) {
                    $defaultStore = $store;
                    break;
                }
            }

            if (!$defaultStore) {
                $this->logger->addError('No active store found.');
                $this->lastError = ['message' => 'No active store found.'];
                return false;
            }
        }

        $defaultStoreBaseUrl = rtrim($defaultStore->getBaseUrl(), '/');
        $webhookUrl = $defaultStoreBaseUrl . self::WEBHOOK_PATH;

        $urls = [$webhookUrl];
        $currentUrls = $this->getUrl($apiKey);
        if ($currentUrls === false && !empty($this->lastError)) {
            return false;
        }
        if (is_array($currentUrls) && !empty($currentUrls)) {
            // Remove any URLs that contain the webhook path
            $currentUrls = array_filter($currentUrls, function ($url) {
                return strpos($url, self::WEBHOOK_PATH) === false;
            });
            $currentUrls = array_values($currentUrls);

            $urls = $currentUrls;
            // add webhook URL
            if (!in_array($webhookUrl, $currentUrls)) {
                $urls[] = $webhookUrl;
            }
        }

        $param = [
            'urls' => $urls,
            'disabled' => false,
        ];
        $log = ['param' => $param];
        try {
            $response = $this->client->put($apiKey, self::ENDPOINT, $param);
            $log['response'] = $this->getSafeSettingsLog($response);
            $this->logger->addInfo(__METHOD__, $log, true);
            if ($this->client->success() && is_array($response) && isset($response['urls'])) {
                return $response['urls'];
            }
            $this->lastError = $this->getClientError();
        } catch (\Exception $e) {
            $log['exception_error'] = $e->getMessage();
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addInfo(__METHOD__, $log, true);
            $this->lastError = $this->getClientError();
        }
        return false;
    }

    /**
     * Return whitelisted API error fields without credentials or response bodies.
     *
     * @return array
     */
    private function getClientError()
    {
        $error = [];
        $httpError = $this->client->getHttpError();
        if (is_scalar($httpError) && (string)$httpError !== '') {
            $error['http_error'] = (string)$httpError;
        }

        $clientErrors = $this->client->getErrors();
        foreach (['request_id', 'code', 'error', 'message'] as $key) {
            if (isset($clientErrors[$key]) && is_scalar($clientErrors[$key])) {
                $error[$key] = (string)$clientErrors[$key];
            }
        }

        return $error ?: ['message' => 'Webhook API request failed.'];
    }

    /**
     * Keep webhook settings logs free of credentials returned by Frisbii.
     *
     * @param mixed $response
     * @return array
     */
    private function getSafeSettingsLog($response)
    {
        if (!is_array($response)) {
            return [];
        }

        return [
            'urls' => isset($response['urls']) && is_array($response['urls']) ? $response['urls'] : [],
            'disabled' => isset($response['disabled']) ? (bool)$response['disabled'] : null,
        ];
    }
}
