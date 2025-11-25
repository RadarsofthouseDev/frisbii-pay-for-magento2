<?php

namespace Radarsofthouse\Reepay\Observer;

class SendPaymentLink implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Radarsofthouse\Reepay\Helper\Charge
     */
    protected $_reepayCharge;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Session
     */
    protected $_reepaySession;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $_reepayHelper;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Email
     */
    protected $_reepayEmail;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Payment
     */
    protected $_reepayPayment;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * Constructor
     *
     * @param \Radarsofthouse\Reepay\Helper\Charge $reepayCharge
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Radarsofthouse\Reepay\Helper\Session $reepaySession
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Radarsofthouse\Reepay\Helper\Email $reepayEmail
     * @param \Radarsofthouse\Reepay\Helper\Payment $reepayPayment
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Radarsofthouse\Reepay\Helper\Charge $reepayCharge,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Radarsofthouse\Reepay\Helper\Session $reepaySession,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Radarsofthouse\Reepay\Helper\Email $reepayEmail,
        \Radarsofthouse\Reepay\Helper\Payment $reepayPayment,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->_reepayCharge = $reepayCharge;
        $this->_scopeConfig = $scopeConfig;
        $this->_reepaySession = $reepaySession;
        $this->_reepayHelper = $reepayHelper;
        $this->_reepayEmail = $reepayEmail;
        $this->_reepayPayment = $reepayPayment;
        $this->_logger = $logger;
        $this->_state = $state;
        $this->_messageManager = $messageManager;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * Observe checkout_submit_all_after
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order == null) {
            // multiple shipping address checkout
            $this->_logger->addDebug(__METHOD__, ["Order object is null, might be multiple shipping address checkout"]);
            return;
        }

        $this->_logger->addDebug(__METHOD__, [$order->getIncrementId()]);
        if ($this->_state->getAreaCode() == 'adminhtml') {
            $orderPaymentMethod = $order->getPayment()->getMethodInstance()->getCode();
            $this->_logger->addDebug(__METHOD__, [$orderPaymentMethod]);
            if ($this->_reepayHelper->isReepayPaymentMethod($orderPaymentMethod)) {
                try {
                    $sessionId = $this->_reepayPayment->createReepaySession($order);

                    if (empty($sessionId)) {
                        $this->_logger->addDebug("Cannot create Frisbii payment session");
                        $this->_messageManager->addError(__('Cannot create Frisbii payment session'));

                        return;
                    }
                    $this->_logger->addDebug("SEND EMAIL");
                    $this->_reepayEmail->sendPaymentLinkEmail($order, $sessionId);

                    $storeId = $order->getStoreId();
                    $statusToSet = $this->_reepayHelper->getConfig('order_status_after_send_payment_link', $storeId);
                    if (!empty($statusToSet) && $statusToSet != '0') {
                        try {
                            $order->setStatus($statusToSet);
                            $order->addStatusHistoryComment(__('Order status changed to %1 after sending payment link to customer.', $statusToSet));
                            $this->_orderRepository->save($order);
                            $this->_logger->addDebug(__METHOD__ . " Setting order status to : " . $statusToSet);
                        } catch (\Exception $e) {
                            $this->_logger->addError(__METHOD__ . " Exception : " . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    $this->_logger->addError(__METHOD__ . " Exception : " . $e->getMessage());
                    $this->_messageManager->addException($e, $e->getMessage());
                }
            }
        }
    }
}
