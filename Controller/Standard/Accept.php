<?php

namespace Radarsofthouse\Reepay\Controller\Standard;

use Magento\Framework\App\ResponseInterface;

class Accept extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $_orderInterface;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Charge
     */
    protected $_reepayCharge;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Session
     */
    protected $_reepaySession;

    /**
     * @var \Radarsofthouse\Reepay\Helper\PaymentSessionValidator
     */
    protected $_paymentSessionValidator;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $_reepayHelper;

    /**
     * @var \Radarsofthouse\Reepay\Model\Status
     */
    protected $_reepayStatus;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $_orderSender;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Email
     */
    protected $_reepayHelperEmail;

    /**
     * @var \Radarsofthouse\Reepay\Helper\SurchargeFee
     */
    protected $_reepayHelperSurchargeFee;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $_resultRedirectFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Radarsofthouse\Reepay\Helper\Charge $reepayCharge
     * @param \Radarsofthouse\Reepay\Helper\Session $reepaySession
     * @param \Radarsofthouse\Reepay\Helper\PaymentSessionValidator $paymentSessionValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Radarsofthouse\Reepay\Helper\Email $reepayHelperEmail
     * @param \Radarsofthouse\Reepay\Helper\SurchargeFee $reepayHelperSurchargeFee
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Radarsofthouse\Reepay\Model\Status $reepayStatus
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Radarsofthouse\Reepay\Helper\Charge $reepayCharge,
        \Radarsofthouse\Reepay\Helper\Session $reepaySession,
        \Radarsofthouse\Reepay\Helper\PaymentSessionValidator $paymentSessionValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Radarsofthouse\Reepay\Helper\Email $reepayHelperEmail,
        \Radarsofthouse\Reepay\Helper\SurchargeFee $reepayHelperSurchargeFee,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Radarsofthouse\Reepay\Model\Status $reepayStatus,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_request = $request;
        $this->_orderInterface = $orderInterface;
        $this->_url = $context->getUrl();
        $this->_reepayCharge = $reepayCharge;
        $this->_reepaySession = $reepaySession;
        $this->_paymentSessionValidator = $paymentSessionValidator;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_reepayHelper = $reepayHelper;
        $this->_reepayHelperEmail = $reepayHelperEmail;
        $this->_reepayHelperSurchargeFee = $reepayHelperSurchargeFee;
        $this->_logger = $logger;
        $this->_reepayStatus = $reepayStatus;
        $this->_priceHelper = $priceHelper;
        $this->_orderSender = $orderSender;
        $this->_checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * Execute
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        $orderId = isset($params['invoice']) && is_scalar($params['invoice'])
            ? trim((string)$params['invoice'])
            : '';
        $id = isset($params['id']) && is_scalar($params['id'])
            ? trim((string)$params['id'])
            : '';
        $isAjax = isset($params['_isAjax']) ? 1 : 0;

        $this->_logger->addDebug(__METHOD__, [
            'invoice' => $orderId,
            'is_ajax' => (bool)$isAjax,
        ]);

        if (!$this->_paymentSessionValidator->isValid($orderId, $id)) {
            $this->_logger->addError('Rejected unbound payment accept callback.', [
                'invoice' => $orderId,
            ]);

            return $this->rejectCallback($isAjax);
        }

        $order = $this->_orderInterface->loadByIncrementId($orderId);
        if (!$order->getId()) {
            return $this->rejectCallback($isAjax);
        }

        $apiKey = $this->_reepayHelper->getApiKey($order->getStoreId());

        try {
            $charge = $this->_reepayCharge->get($apiKey, $order->getIncrementId());
        } catch (\Exception $exception) {
            $this->_logger->addError('Unable to verify Frisbii charge for payment accept callback.', [
                'invoice' => $order->getIncrementId(),
                'error' => $exception->getMessage(),
            ]);

            return $this->rejectCallback($isAjax);
        }

        $chargeState = is_array($charge) && isset($charge['state'])
            ? (string)$charge['state']
            : '';
        if (!in_array($chargeState, ['authorized', 'settled'], true)) {
            $this->_logger->addError('Rejected payment accept callback with unsuccessful Frisbii charge state.', [
                'invoice' => $order->getIncrementId(),
                'state' => $chargeState !== '' ? $chargeState : 'unknown',
            ]);

            return $this->rejectCallback($isAjax);
        }

        $this->_checkoutSession->setLastOrderId($order->getId());
        $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->_checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
        $this->_checkoutSession->setLastQuoteId($order->getQuoteId());

        if ($isAjax === 1) {
            $result = [];
            $result['status'] = 'success';
            if (!empty($order->getRemoteIp())) {
                // place online
                $result['redirect_url'] = $this->_url->getUrl('checkout/onepage/success');
            } else {
                // place by admin
                $result['redirect_url'] = $this->_url->getUrl('reepay/standard/success');
            }
            return $this->_resultJsonFactory->create()->setHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0',
                true
            )->setData($result);
        }
        $this->_logger->addDebug('Redirect to checkout/onepage/success');
        if (!empty($order->getRemoteIp())) {
            // place online
            return $this->redirect('checkout/onepage/success');
        } // place by admin
        return $this->redirect('reepay/standard/success');
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

        return $this->_resultJsonFactory->create()
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
        $resultPage = $this->_resultRedirectFactory->create()->setPath($path);
        $resultPage->setHeader(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0',
            true
        );
        return $resultPage;
    }
}
