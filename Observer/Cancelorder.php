<?php

namespace Radarsofthouse\Reepay\Observer;

class Cancelorder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Radarsofthouse\Reepay\Helper\Charge
     */
    private $reepayCharge;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Session
     */
    private $reepaySession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $reepayHelper;

    /**
     * @var \Radarsofthouse\Reepay\Api\SessionRepositoryInterface
     */
    protected $reepaySessionRepository;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $logger;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Refund
     */
    protected $reepayRefund;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Invoice
     */
    protected $reepayInvoice;

    /**
     * Constructor
     *
     * @param \Radarsofthouse\Reepay\Helper\Charge $reepayCharge
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Radarsofthouse\Reepay\Helper\Session $reepaySession
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Radarsofthouse\Reepay\Api\SessionRepositoryInterface $reepaySessionRepository
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Radarsofthouse\Reepay\Helper\Refund $reepayRefund
     * @param \Radarsofthouse\Reepay\Helper\Invoice $reepayInvoice
     */
    public function __construct(
        \Radarsofthouse\Reepay\Helper\Charge $reepayCharge,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Radarsofthouse\Reepay\Helper\Session $reepaySession,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Radarsofthouse\Reepay\Api\SessionRepositoryInterface $reepaySessionRepository,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Radarsofthouse\Reepay\Helper\Refund $reepayRefund,
        \Radarsofthouse\Reepay\Helper\Invoice $reepayInvoice
    ) {
        $this->reepayCharge = $reepayCharge;
        $this->scopeConfig = $scopeConfig;
        $this->reepaySession = $reepaySession;
        $this->reepayHelper = $reepayHelper;
        $this->reepaySessionRepository = $reepaySessionRepository;
        $this->logger = $logger;
        $this->reepayRefund = $reepayRefund;
        $this->reepayInvoice = $reepayInvoice;
    }

    /**
     * Observe order_cancel_after
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getData('order');

        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        if ($this->reepayHelper->isReepayPaymentMethod($paymentMethod)) {
            $this->logger->addDebug(__METHOD__, ['order ID : ' . $order->getIncrementId()]);

            $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());

            $charge = $this->reepayCharge->get(
                $apiKey,
                $order->getIncrementId()
            );

            $reepayMethod = isset($charge['source']['type']) ? $charge['source']['type'] : '';

            $isAutoCapture = false;
            if (
                $this->reepayHelper->getConfig('auto_capture', $order->getStoreId()) ||
                $this->reepayHelper->isReepayMethodAutoCapture($paymentMethod, $reepayMethod) ||
                $order->getPayment()->getMethodInstance()->isAutoCapture()
            ) {
                $isAutoCapture = true;
            }

            $chargeState = is_array($charge) && isset($charge['state']) ? $charge['state'] : '';

            if (in_array($chargeState, ['created', 'failed', 'pending'], true)) {
                $cancelRes = $this->reepayInvoice->cancel(
                    $apiKey,
                    $order->getIncrementId()
                );

                $this->logger->addDebug("Cancel invoice for order #" . $order->getIncrementId());

                if (!empty($cancelRes)) {
                    if ($cancelRes['state'] == 'cancelled') {
                        $_payment = $order->getPayment();
                        $this->reepayHelper->setReepayPaymentState($_payment, 'cancelled');
                    }
                }
            } elseif ($chargeState == 'settled' && $isAutoCapture) {
                try {
                    $settledAmount = $charge['amount'];

                    $options = [];
                    $options['invoice'] = $order->getIncrementId();
                    $options['amount'] = $this->reepayHelper->toInt($settledAmount);
                    $options['ordertext'] = "refund";

                    $refund = $this->reepayRefund->create(
                        $apiKey,
                        $options
                    );

                    if (!empty($refund)) {
                        if (isset($refund["error"])) {
                            $this->logger->addDebug("refund error : ", $refund);
                            $error_message = $refund["error"];
                            if (isset($refund["message"])) {
                                $error_message = $refund["error"] . " : " . $refund["message"];
                            }
                            throw new \Magento\Framework\Exception\LocalizedException(__($error_message));
                        }
                    }
                    $this->logger->addError("Order cancelation: Refunded order #" . $order->getIncrementId() . ", refund amount : " . ($settledAmount / 100));
                } catch (\Magento\Framework\Exception\LocalizedException | \Exception $e) {
                    $this->logger->addError("Order cancelation: Refund error for order #" . $order->getIncrementId() . ' : ' . $e->getMessage());
                }
            } elseif ($chargeState == 'cancelled') {
                $_payment = $order->getPayment();
                $this->reepayHelper->setReepayPaymentState($_payment, 'cancelled');
            } elseif ($chargeState == 'authorized') {
                $cancelRes = $this->reepayCharge->cancel(
                    $apiKey,
                    $order->getIncrementId()
                );

                $this->logger->addDebug("Cancel charge for order #" . $order->getIncrementId());

                if (!empty($cancelRes)) {
                    if ($cancelRes['state'] == 'cancelled') {
                        $_payment = $order->getPayment();
                        $this->reepayHelper->setReepayPaymentState($_payment, 'cancelled');
                    }
                }
            }
        }

        return $this;
    }
}
