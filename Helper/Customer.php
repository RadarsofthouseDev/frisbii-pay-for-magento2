<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Radarsofthouse\Reepay\Api\CustomerRepositoryInterface;
use Radarsofthouse\Reepay\Api\Data\CustomerInterfaceFactory;
use Radarsofthouse\Reepay\Client\Api;

class Customer extends AbstractHelper
{
    const ENDPOINT = 'customer';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var \Radarsofthouse\Reepay\Client\Api
     */
    private $client = null;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    private $logger = null;

    /**
     * Constructor
     *
     * @param \Radarsofthouse\Reepay\Api\CustomerRepositoryInterface $customerRepository
     * @param \Radarsofthouse\Reepay\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        Context $context,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->client = new Api();
        $this->logger = $logger;
    }

    /**
     * Get customer by email.
     *
     * @param string $apiKey
     * @param string $email
     * @return false|string
     */
    public function search($apiKey, $email)
    {
        $log = ['param' => ['email' => $email]];
        if (empty($email)) {
            $log['input_error'] = 'empty email.';
            $this->logger->addInfo(__METHOD__, $log, true);
            return false;
        }
        $param = [
            'size' => 10,
            'range' => 'created',
            'from' => '1970-01-01',
            'email' => "$email",
        ];
        try {
            $response = $this->client->get($apiKey, 'list/' . self::ENDPOINT, $param);
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);
            if ($this->client->success() && array_key_exists('count', $response) && (int)$response['count'] > 0) {
                foreach ($response['content'] as $index => $item) {
                    if (!array_key_exists('deleted', $item) || empty($item['deleted'])) {
                        return $item['handle'];
                    }
                }
            }
        } catch (\Exception $e) {
            $log['exception_error'] = $e->getMessage();
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addInfo(__METHOD__, $log, true);
        }
        return false;
    }

    /**
     * Get Payment Cards
     *
     * @param string $apiKey
     * @param string $handle
     * @return array|mixed
     * @throws \Exception
     */
    public function getPaymentCards($apiKey, $handle)
    {
        $log = ['param' => ['handle' => $handle]];
        $response = $this->client->get($apiKey, self::ENDPOINT . "/{$handle}/payment_method");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);
            if (array_key_exists('cards', $response) && !empty($response['cards'])) {
                return $response['cards'];
            }
            return [];
        }
        $log['http_errors'] = $this->client->getHttpError();
        $log['response_errors'] = $this->client->getErrors();
        $this->logger->addError(__METHOD__, $log, true);
        return [];
    }

    /**
     * Get Payment Cards By Customer
     *
     * @param string $apiKey
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array|mixed
     */
    public function getPaymentCardsByCustomer($apiKey, $customer)
    {
        $handle = null;
        $notFound = false;
        try {
            $customerId = $customer->getId();
            if ($customerId) {
                $reepayCustomer = $this->customerRepository->getByMagentoCustomerId($customerId);
                $handle = $reepayCustomer->getHandle();
            }
        } catch (NoSuchEntityException | LocalizedException $exception) {
            $notFound = true;
            $customerEmail = $customer->getEmail();
            if ($customerEmail) {
                $handle = $this->search($apiKey, $customer->getEmail());
            }
        } catch (\Exception $e) {
            $this->logger->addError("Error : getPaymentCardsByCustomer : " . $e->getMessage());
        }
        if ($handle) {
            try {
                if ($notFound) {
                    /** @var \Radarsofthouse\Reepay\Api\Data\CustomerInterface $reepayCustomer */
                    $reepayCustomer = $this->customerFactory->create();
                    $reepayCustomer->setMagentoCustomerId($customer->getId());
                    $reepayCustomer->setMagentoEmail($customer->getEmail());
                    $reepayCustomer->setHandle($handle);
                    $this->customerRepository->save($reepayCustomer);
                }
            } catch (LocalizedException $exception) {
                $this->logger->addError("Error : getPaymentCardsByCustomer : " . $exception->getMessage());
            }
            try {
                return $this->getPaymentCards($apiKey, $handle);
            } catch (\Exception $e) {
                $this->logger->addError("Error : getPaymentCardsByCustomer -> getPaymentCards : " . $e->getMessage());
            }
        }
        return [];
    }

    /**
     * Check whether a saved payment card belongs to the Magento customer.
     *
     * @param string $apiKey
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $cardId
     * @return bool
     */
    public function isPaymentCardOwnedByCustomer($apiKey, $customer, $cardId)
    {
        $cards = $this->getPaymentCardsByCustomer($apiKey, $customer);
        if (!is_array($cards)) {
            return false;
        }

        foreach ($cards as $card) {
            if (
                is_array($card)
                && isset($card['id'])
                && is_scalar($card['id'])
                && hash_equals((string)$card['id'], (string)$cardId)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete Payment Card
     *
     * @param string $apiKey
     * @param string $handle
     * @param string $cardId
     * @return bool|mixed
     */
    public function deletePaymentCard($apiKey, $handle, $cardId)
    {
        $log = ['param' => ['handle' => $handle, 'cardId' => $cardId]];
        $response = $this->client->delete($apiKey, self::ENDPOINT . "/{$handle}/payment_method/{$cardId}");
        if ($this->client->success()) {
            $log['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);
            return true;
        }
        $log['http_errors'] = $this->client->getHttpError();
        $log['response_errors'] = $this->client->getErrors();
        $this->logger->addError(__METHOD__, $log, true);
        return false;
    }

    /**
     * Delete Payment Card By Customer ID
     *
     * @param string $apiKey
     * @param string $customerId
     * @param string $cardId
     * @return bool|mixed
     */
    public function deletePaymentCardByCustomerId($apiKey, $customerId, $cardId)
    {
        $log = ['param' => ['customerId' => $customerId, 'cardId' => $cardId]];
        try {
            $reepayCustomer = $this->customerRepository->getByMagentoCustomerId($customerId);
            $handle = $reepayCustomer->getHandle();
            return $this->deletePaymentCard($apiKey, $handle, $cardId);
        } catch (NoSuchEntityException | LocalizedException | \Exception $e) {
            $this->logger->addError("Error : deletePaymentCardByCustomerId : " . $e->getMessage());
        }
        return false;
    }
}
