<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Radarsofthouse\Reepay\Client\Api;

class Invoice extends AbstractHelper
{
    const ENDPOINT = 'invoice';

    /**
     * @var \Radarsofthouse\Reepay\Client\Api
     */
    private $client = null;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    private $logger = null;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     */
    public function __construct(Context $context, Logger $logger)
    {
        parent::__construct($context);
        $this->client = new Api();
        $this->logger = $logger;
    }

    /**
     * List invoices.
     *
     * @param string $apiKey
     * @param int $page
     * @param int $size
     * @param string $search
     * @param string $sort
     * @return bool|array
     * @throws \Exception
     */
    public function lists($apiKey, $page = 0, $size = 100, $search = '', $sort = '-created')
    {
        $log = ['param' => ['page' => $page, 'size' => $size, 'search' => $search, 'sort' => $sort]];
        $data = [
            'size' => $size,
            'sort' => $sort,
        ];
        if ($page) {
            $data['page'] = $page;
        }
        if (!empty($search)) {
            $data['search'] = $search;
        }
        $this->client->setPrivateKey($apiKey);
        $response = $this->client->get($apiKey, self::ENDPOINT, $data);
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Get invoice by ID or handle
     *
     * @param string $apiKey
     * @param string $handle
     * @return bool|array
     * @throws \Exception
     */
    public function get($apiKey, $handle)
    {
        $log = ['param' => ['handle' => $handle]];
        $response = $this->client->get($apiKey, self::ENDPOINT . "/{$handle}");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Cancel invoice.
     * An invoice with all transactions with no or only failed transaction can be cancelled.
     * No further attempts to fulfill the invoice will be made.
     * If the invoice is dunning the dunning process will be cancelled.
     *
     * @param string $apiKey
     * @param string $handle
     * @return bool|array
     * @throws \Exception
     */
    public function cancel($apiKey, $handle)
    {
        $log = ['param' => ['handle' => $handle]];
        $response = $this->client->post($apiKey, self::ENDPOINT . "/{$handle}/cancel");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Check that a value contains only allowed characters for a single URL path segment.
     *
     * @param mixed $value
     * @return bool
     */
    public static function isValidIdentifier($value)
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9_-]+$/', $value) === 1;
    }

    /**
     *  Get transaction by ID
     *
     * @param string $apiKey
     * @param string $handle
     * @param string $transaction
     * @return bool|array
     * @throws \Exception
     */
    public function getTransaction($apiKey, $handle, $transaction)
    {
        $log = ['param' => ['handle' => $handle, 'transaction' => $transaction]];
        if (!self::isValidIdentifier($handle) || !self::isValidIdentifier($transaction)) {
            $this->logger->addError(__METHOD__ . ' : invalid handle or transaction ID', $log, true);

            return false;
        }
        $response = $this->client->get($apiKey, self::ENDPOINT . "/{$handle}/transaction/{$transaction}");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Get transaction details.
     *
     * @param string $apiKey
     * @param string $handle
     * @param string $transaction
     * @return bool|array
     * @throws \Exception
     */
    public function getTransactionDetails($apiKey, $handle, $transaction)
    {
        $log = ['param' => ['handle' => $handle, 'transaction' => $transaction]];
        if (!self::isValidIdentifier($handle) || !self::isValidIdentifier($transaction)) {
            $this->logger->addError(__METHOD__ . ' : invalid handle or transaction ID', $log, true);

            return false;
        }
        $response = $this->client->get($apiKey, self::ENDPOINT . "/{$handle}/transaction/{$transaction}/details");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Cancel transaction.
     *
     * @param string $apiKey
     * @param string $handle
     * @param string $transaction
     * @return bool|array
     * @throws \Exception
     */
    public function cancelTransaction($apiKey, $handle, $transaction)
    {
        $log = ['param' => ['handle' => $handle, 'transaction' => $transaction]];
        $response = $this->client->post($apiKey, self::ENDPOINT . "/{$handle}/transaction/{$transaction}/cancel");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Offline manual settle.
     * A non-settled invoice can be settled using an offline manual transfer.
     * An offline manual transfer could for example be a cash or bank transfer not handled automatically by Reepay.
     * The invoice will be instantly settled and a receipt email is sent to the customer.
     *
     * @param string $apiKey
     * @param string $handle
     * @param string $method
     * @param string $payment_date
     * @param string $comment
     * @param string $reference
     * @return bool|array
     * @throws \Exception
     */
    public function offlineManualSettle($apiKey, $handle, $method, $payment_date, $comment = '', $reference = '')
    {
        $log = [
            'param' => [
                'handle' => $handle,
                'method' => $method,
                'payment_date' => $payment_date,
                'comment' => $comment,
                'reference' => $reference,
            ],
        ];
        $settle = [
            'method' => $method,
            'payment_date' => $payment_date,
            'comment' => $comment,
            'reference' => $reference,
        ];
        $response = $this->client->post($apiKey, self::ENDPOINT . "/{$handle}/manual_settle", $settle);
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Invoice reactivate.
     * A failed or cancelled invoice can be put back to state pending for processing.
     * The invoice will potentially enter a new dunning process if it is a subscription invoice.
     *
     * @param string $apiKey
     * @param string $handle
     * @return bool|array
     * @throws \Exception
     */
    public function reactivate($apiKey, $handle)
    {
        $log = ['param' => ['handle' => $handle]];
        $response = $this->client->post($apiKey, self::ENDPOINT . "/{$handle}/reactivate");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Cancel settle later.
     *
     * @param string $apiKey
     * @param string $handle
     * @return bool|array
     * @throws \Exception
     */
    public function cancelSettleLater($apiKey, $handle)
    {
        $log = ['param' => ['handle' => $handle]];
        $response = $this->client->post($apiKey, self::ENDPOINT . "/{$handle}/settle/cancel");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }

    /**
     * Get invoice metadata by handle
     *
     * @param string $apiKey
     * @param string $handle
     * @return bool|array
     * @throws \Exception
     */
    public function getMetadata($apiKey, $handle)
    {
        $log = ['param' => ['handle' => $handle]];
        if (!self::isValidIdentifier($handle)) {
            $this->logger->addError(__METHOD__ . ' : invalid handle', $log, true);

            return false;
        }
        $response = $this->client->get($apiKey, self::ENDPOINT . "/$handle/metadata");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);
            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);
            return false;
        }
    }
}
