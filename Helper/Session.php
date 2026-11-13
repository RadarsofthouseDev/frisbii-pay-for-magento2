<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Radarsofthouse\Reepay\Client\Checkout;

class Session extends AbstractHelper
{
    const ENDPOINT = 'session';

    /**
     * @var \Radarsofthouse\Reepay\Client\Checkout
     */
    protected $client = null;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $logger = null;

    /**
     * constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Radarsofthouse\Reepay\Helper\Logger $logger
    ) {
        parent::__construct($context);
        $this->client = new Checkout();
        $this->logger = $logger;
    }

    /**
     * Create charge session
     *
     * @param string $apiKey
     * @param array $session
     * @return bool|mixed
     * @throws \Exception
     */
    public function chargeCreate($apiKey, $session)
    {
        $log = ['param' => ['session' => $session]];
        $response = $this->client->post($apiKey, self::ENDPOINT . '/charge', $session);
        if ($this->client->success()) {
            $log ['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $httpError = $this->client->getHttpError();
            $responseErrors = $this->client->getErrors();
            $errorMessage = '';
            if (is_array($responseErrors) && !empty($responseErrors['message'])) {
                $errorMessage = $responseErrors['message'];
            } elseif (is_array($responseErrors) && !empty($responseErrors['error'])) {
                $errorMessage = $responseErrors['error'];
            } elseif ($httpError) {
                $errorMessage = $httpError;
            } else {
                $errorMessage = 'Frisbii checkout session creation failed.';
            }

            $log['http_errors'] = $httpError;
            $log['response_errors'] = $responseErrors;
            $log['error_code'] = is_array($responseErrors) && isset($responseErrors['code'])
                ? $responseErrors['code']
                : null;
            $log['error_message'] = $errorMessage;
            $this->logger->addError(__METHOD__ . ' Failed to create Frisbii checkout session.', $log, true);

            return false;
        }
    }

    /**
     * Create session charge with create exist invoice.
     *
     * @param string $apiKey
     * @param string $invoice
     * @param array $paymentMethods
     * @param bool $settle
     * @param array $option
     * @return bool|array
     * @throws \Exception
     */
    public function chargeCreateWithExistInvoice(
        $apiKey,
        $invoice,
        $paymentMethods,
        $settle,
        $option = []
    ) {
        $option['invoice'] = $invoice;
        $option['settle'] = $settle;
        $option['payment_methods'] = $paymentMethods;

        return $this->chargeCreate($apiKey, $option);
    }

    /**
     * Create session charge with create exist customer.
     *
     * @param string $apiKey
     * @param string $customerHandle
     * @param array $order
     * @param array $paymentMethods
     * @param bool $settle
     * @param array $option
     * @return bool|array
     * @throws \Exception
     */
    public function chargeCreateWithExistCustomer(
        $apiKey,
        $customerHandle,
        $order,
        $paymentMethods,
        $settle,
        $option = []
    ) {
        $order['customer_handle'] = $customerHandle;
        $order['settle'] = $settle;
        $option['order'] = $order;
        $option['settle'] = $settle;
        $option['payment_methods'] = $paymentMethods;

        return $this->chargeCreate($apiKey, $option);
    }

    /**
     * Create session charge with create new customer.
     *
     * @param string $apiKey
     * @param array $customer
     * @param array $order
     * @param array $paymentMethods
     * @param bool $settle
     * @param array $option
     * @return bool|array
     * @throws \Exception
     */
    public function chargeCreateWithNewCustomer(
        $apiKey,
        $customer,
        $order,
        $paymentMethods,
        $settle,
        $option = []
    ) {
        $order['customer'] = $customer;
        $order['settle'] = $settle;
        $option['order'] = $order;
        $option['settle'] = $settle;
        $option['payment_methods'] = $paymentMethods;

        return $this->chargeCreate($apiKey, $option);
    }

    /**
     * Delete session
     *
     * @param string $apiKey
     * @param string $id
     * @return bool|array
     * @throws \Exception
     */
    public function delete($apiKey, $id)
    {
        $log = ['param' => ['id' => $id]];
        $response = $this->client->delete($apiKey, self::ENDPOINT . "/{$id}");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return true;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }
}
