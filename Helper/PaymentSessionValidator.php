<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Radarsofthouse\Reepay\Api\SessionRepositoryInterface;

class PaymentSessionValidator extends AbstractHelper
{
    /**
     * @var SessionRepositoryInterface
     */
    private $sessionRepository;

    /**
     * @param Context $context
     * @param SessionRepositoryInterface $sessionRepository
     */
    public function __construct(
        Context $context,
        SessionRepositoryInterface $sessionRepository
    ) {
        parent::__construct($context);
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * Check that a provider session handle belongs to the supplied order.
     *
     * @param mixed $orderNumber
     * @param mixed $handle
     * @return bool
     */
    public function isValid($orderNumber, $handle)
    {
        if (!is_scalar($orderNumber) || !is_scalar($handle)) {
            return false;
        }

        $orderNumber = trim((string)$orderNumber);
        $handle = trim((string)$handle);
        if ($orderNumber === '' || $handle === '') {
            return false;
        }

        try {
            $sessions = $this->sessionRepository->getListByOrderNumber($orderNumber);
            foreach ($sessions->getItems() as $session) {
                if (
                    (string)$session->getHandle() === $handle
                    && (string)$session->getChargeHandle() === $orderNumber
                    && (string)$session->getOrderNumber() === $orderNumber
                ) {
                    return true;
                }
            }
        } catch (\Exception $exception) {
            return false;
        }

        return false;
    }
}
