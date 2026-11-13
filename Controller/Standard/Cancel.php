<?php

namespace Radarsofthouse\Reepay\Controller\Standard;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $orderInterface;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Session
     */
    private $reepaySession;

    /**
     * @var \Radarsofthouse\Reepay\Helper\PaymentSessionValidator
     */
    private $paymentSessionValidator;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $reepayHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory
     */
    protected $transactionSearchResultInterfaceFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Radarsofthouse\Reepay\Helper\Session $reepaySession
     * @param \Radarsofthouse\Reepay\Helper\PaymentSessionValidator $paymentSessionValidator
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Radarsofthouse\Reepay\Helper\Session $reepaySession,
        \Radarsofthouse\Reepay\Helper\PaymentSessionValidator $paymentSessionValidator,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
        $this->orderInterface = $orderInterface;
        $this->checkoutSession = $checkoutSession;
        $this->url = $context->getUrl();
        $this->reepaySession = $reepaySession;
        $this->paymentSessionValidator = $paymentSessionValidator;
        $this->reepayHelper = $reepayHelper;
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->transactionSearchResultInterfaceFactory = $transactionSearchResultInterfaceFactory;

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->request->getParams();
        $orderId = isset($params['invoice']) && is_scalar($params['invoice'])
            ? trim((string)$params['invoice'])
            : '';
        $id = isset($params['id']) && is_scalar($params['id'])
            ? trim((string)$params['id'])
            : '';
        $isAjax = isset($params['_isAjax']) ? 1 : 0;

        $this->logger->addDebug(__METHOD__, [
            'invoice' => $orderId,
            'is_ajax' => (bool)$isAjax,
        ]);

        if (!$this->paymentSessionValidator->isValid($orderId, $id)) {
            $this->logger->addError('Rejected unbound payment cancel callback.', [
                'invoice' => $orderId,
            ]);

            return $this->rejectCallback($isAjax);
        }

        $order = $this->orderInterface->loadByIncrementId($orderId);
        if (!$order->getId()) {
            return $this->rejectCallback($isAjax);
        }

        $cancelConfig = $this->reepayHelper->getConfig('cancel_order_after_payment_cancel', $order->getStoreId());

        if ($cancelConfig && $order->canCancel()) {

            $transactions = $this->transactionSearchResultInterfaceFactory->create()->addOrderIdFilter($order->getId());

            // don't allowed the cancelation if already have transactions (payment is paid)
            if (count($transactions->getItems()) == 0) {
                $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
                $order->cancel();
                $order->addStatusHistoryComment('Frisbii : order have been cancelled by payment page');
                $order->save();
                $this->logger->addDebug('Cancelled order : ' . $orderId);
                $payment = $order->getPayment();
                $this->reepayHelper->setReepayPaymentState($payment, 'cancelled');
                // delete reepay session
                $sessionRes = $this->reepaySession->delete(
                    $apiKey,
                    $id
                );
                $this->checkoutSession->restoreQuote();
                $this->checkoutSession->unsLastQuoteId()
                    ->unsLastSuccessQuoteId()
                    ->unsLastOrderId()
                    ->unsLastRealOrderId();
            } else {
                $this->logger->addDebug('The payment is done : ignore cancellation for order ' . $orderId);
            }
        }

        // unset reepay session id on checkout session
        /*
        if ($this->checkoutSession->getReepaySessionID() && $this->checkoutSession->getReepaySessionOrder()) {
            $this->checkoutSession->unsReepaySessionID();
            $this->checkoutSession->unsReepaySessionOrder();
        }
        */

        if ($isAjax === 1) {
            $result = [
                'status' => 'success',
                'redirect_url' => $this->url->getUrl('checkout/cart'),
            ];
            return $this->resultJsonFactory->create()
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true)
                ->setData($result);
        }
        if (!$cancelConfig) {
            return $this->redirect('/');
        }
        return $this->redirect('checkout/cart');
    }

    /**
     * Reject a callback without revealing whether the order exists.
     *
     * @param bool $isAjax
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function rejectCallback($isAjax)
    {
        if (!$isAjax) {
            return $this->redirect('checkout/cart');
        }

        return $this->resultJsonFactory->create()
            ->setHttpResponseCode(400)
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true)
            ->setData([
                'status' => 'error',
                'message' => 'Invalid payment session.',
            ]);
    }

    /**
     * Redirect
     *
     * @param string $path
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function redirect($path)
    {
        $resultPage = $this->resultRedirectFactory->create()->setPath($path);
        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        return $resultPage;
    }
}
