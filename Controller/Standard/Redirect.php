<?php

namespace Radarsofthouse\Reepay\Controller\Standard;

use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Sales\Model\Order;

class Redirect extends \Magento\Framework\App\Action\Action
{
    public const DISPLAY_EMBEDDED = '1';
    public const DISPLAY_OVERLAY = '2';
    public const DISPLAY_WINDOW = '3';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $_reepayHelper;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Payment
     */
    protected $_reepayPayment;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $_logger;

    /**
     * @var \Radarsofthouse\Reepay\Model\Status
     */
    protected $_reepayStatus;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $_resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Radarsofthouse\Reepay\Helper\Payment $reepayPayment
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Radarsofthouse\Reepay\Model\Status $reepayStatus
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Radarsofthouse\Reepay\Helper\Payment $reepayPayment,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Radarsofthouse\Reepay\Model\Status $reepayStatus,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_reepayHelper = $reepayHelper;
        $this->_reepayPayment = $reepayPayment;
        $this->_messageManager = $context->getMessageManager();
        $this->_logger = $logger;
        $this->_reepayStatus = $reepayStatus;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        try {
            $this->_logger->addDebug(__METHOD__, []);
            if (!$this->_objectManager->get(SuccessValidator::class)->isValid()) {
                return $this->redirect();
            }

            // get order ID from onepage checkout session
            $checkoutOnePageSession = $this->_objectManager->get(Onepage::class)->getCheckout();
            $orderId = $checkoutOnePageSession->getLastOrderId();
            $order = $this->_objectManager->create(Order::class)->load($orderId);

            if (!$order->getId()) {
                return $this->redirect();
            }

            // check using saved credit card
            if ($order->getPayment()->getMethodInstance()->getCode() == "reepay_payment") {
                if (!empty($order->getReepayCreditCard()) && $order->getReepayCreditCard() != 'new') {
                    $save_card_type = $this->_reepayHelper->getConfig('save_card_type', $order->getStoreId());
                    if ($save_card_type == 0) {
                        // CIT (Customer Initiated Transaction)

                        $this->_logger->addDebug('Use saved credit card for CIT.');

                        $paymentTransactionId = null;
                        $paymentTransactionId = $this->_reepayPayment->createReepaySession(
                            $order,
                            $order->getReepayCreditCard()
                        );
                        $this->_logger->addDebug('Created payment session for CIT.');

                        $pageTitleConfig = $this->_reepayHelper->getConfig('title', $order->getStoreId());
                        $resultPage->getConfig()
                            ->getTitle()
                            ->set($pageTitleConfig);
                        $template = 'Radarsofthouse_Reepay::standard/window.phtml';
                        $resultPage->getLayout()
                            ->getBlock('reepay_standard_redirect')
                            ->setTemplate($template)
                            ->setPaymentTransactionId($paymentTransactionId);
                        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);

                        return $resultPage;
                    } else {
                        // MIT (Merchant Initiated Transaction)

                        $this->_logger->addDebug('Use saved credit card for MIT.');

                        $createCharge = $this->_reepayPayment->createChargeWithExistCustomer(
                            $order,
                            $order->getReepayCreditCard()
                        );
                        if ($createCharge) {
                            $this->_checkoutSession->setLastOrderId($order->getId());
                            $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
                            $this->_checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                            $this->_checkoutSession->setLastQuoteId($order->getQuoteId());

                            $resultPage = $this->_resultRedirectFactory->create()->setPath('checkout/onepage/success');
                            $resultPage->setHeader(
                                'Cache-Control',
                                'no-store, no-cache, must-revalidate, max-age=0',
                                true
                            );
                            return $resultPage;
                        } else {
                            $this->_messageManager->addError(__('Payment failure. Please try again later.'));
                            return $this->redirect();
                        }
                    }
                }
            }

            $paymentTransactionId = null;
            $paymentTransactionId = $this->_reepayPayment->createReepaySession($order);

            $this->_logger->addDebug('Created payment session.');

            // render reepay/standard/redirect
            $pageTitleConfig = $this->_reepayHelper->getConfig('title', $order->getStoreId());
            $resultPage->getConfig()
                ->getTitle()
                ->set($pageTitleConfig);

            $displayTypeConfig = (string)$this->_reepayHelper->getConfig('display_type', $order->getStoreId());

            if (in_array(
                $order->getPayment()->getMethodInstance()->getCode(),
                [
                    'reepay_viabill',
                    'reepay_vipps',
                    'reepay_applepay',
                    'reepay_ideal'
                ]
            )) {
                // force viabill into payment window always
                $this->_logger->addDebug('Payments : DISPLAY_WINDOW');
                $template = 'Radarsofthouse_Reepay::standard/window.phtml';
            } elseif ($displayTypeConfig === self::DISPLAY_EMBEDDED) {
                $this->_logger->addDebug('DISPLAY_EMBEDDED');
                $template = 'Radarsofthouse_Reepay::standard/embedded.phtml';
            } elseif ($displayTypeConfig === self::DISPLAY_OVERLAY) {
                $this->_logger->addDebug('DISPLAY_OVERLAY');
                $template = 'Radarsofthouse_Reepay::standard/overlay.phtml';
            } elseif ($displayTypeConfig === self::DISPLAY_WINDOW) {
                $this->_logger->addDebug('DISPLAY_WINDOW');
                $template = 'Radarsofthouse_Reepay::standard/window.phtml';
            }
            if (!empty($template)) {
                $resultPage->getLayout()
                    ->getBlock('reepay_standard_redirect')
                    ->setTemplate($template)
                    ->setLogoUrl($this->getLogoUrl())
                    ->setPaymentTransactionId($paymentTransactionId);
            }
        } catch (\Exception $e) {
            $this->_logger->addError(__METHOD__ . " Exception : " . $e->getMessage());
            $this->_messageManager->addExceptionMessage($e, __('Something went wrong, please try again later'));
            return $this->redirect();
        }
        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        return $resultPage;
    }

    /**
     * Redirect
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function redirect()
    {
        $resultPage = $this->_resultRedirectFactory->create()->setPath('checkout/cart');
        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        return $resultPage;
    }

    protected function getLogoUrl()
    {
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        if ($storeLogoPath !== null) {
            return $path;
        }
        return null;
    }
}
