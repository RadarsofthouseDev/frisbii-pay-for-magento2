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
     * Get the Frisbii customer handle linked to a Magento customer.
     *
     * The link is created only by linkMagentoCustomer().
     *
     * @param int|string $magentoCustomerId
     * @return string|null
     */
    public function getLinkedHandle($magentoCustomerId)
    {
        if (empty($magentoCustomerId)) {
            return null;
        }
        try {
            $handle = $this->customerRepository->getByMagentoCustomerId($magentoCustomerId)->getHandle();
            return !empty($handle) ? (string)$handle : null;
        } catch (NoSuchEntityException | LocalizedException $e) {
            return null;
        } catch (\Exception $e) {
            $this->logger->addError("Error : getLinkedHandle : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Link a Magento customer to a Frisbii customer handle, only if no link exists yet.
     *
     * Call this only with a handle that Frisbii returned for a payment made by this
     * logged-in customer.
     *
     * @param int|string $magentoCustomerId
     * @param string $email
     * @param string $handle
     * @return bool
     */
    public function linkMagentoCustomer($magentoCustomerId, $email, $handle)
    {
        if (empty($magentoCustomerId) || empty($handle) || $this->getLinkedHandle($magentoCustomerId) !== null) {
            return false;
        }
        try {
            /** @var \Radarsofthouse\Reepay\Api\Data\CustomerInterface $reepayCustomer */
            $reepayCustomer = $this->customerFactory->create();
            $reepayCustomer->setMagentoCustomerId($magentoCustomerId);
            $reepayCustomer->setMagentoEmail($email);
            $reepayCustomer->setHandle($handle);
            $this->customerRepository->save($reepayCustomer);
            return true;
        } catch (\Exception $e) {
            $this->logger->addError("Error : linkMagentoCustomer : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Payment Cards By Customer
     *
     * Uses only the stored link between the Magento customer and the Frisbii customer.
     * It does not look up or link a Frisbii customer by email.
     *
     * @param string $apiKey
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array|mixed
     */
    public function getPaymentCardsByCustomer($apiKey, $customer)
    {
        $handle = $this->getLinkedHandle($customer->getId());
        if ($handle) {
            try {
                return $this->getPaymentCards($apiKey, $handle);
            } catch (\Exception $e) {
                $this->logger->addError("Error : getPaymentCardsByCustomer -> getPaymentCards : " . $e->getMessage());
            }
        }
        return [];
    }

    /**
     * Check whether a saved payment card belongs to the Frisbii customer handle.
     *
     * @param string $apiKey
     * @param string $handle
     * @param string $cardId
     * @return bool
     */
    public function isPaymentCardOwnedByHandle($apiKey, $handle, $cardId)
    {
        if (empty($handle)) {
            return false;
        }
        try {
            $cards = $this->getPaymentCards($apiKey, $handle);
        } catch (\Exception $e) {
            $this->logger->addError("Error : isPaymentCardOwnedByHandle : " . $e->getMessage());
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
     * Check whether a saved payment card belongs to the Magento customer.
     *
     * @param string $apiKey
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $cardId
     * @return bool
     */
    public function isPaymentCardOwnedByCustomer($apiKey, $customer, $cardId)
    {
        return $this->isPaymentCardOwnedByHandle($apiKey, $this->getLinkedHandle($customer->getId()), $cardId);
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
