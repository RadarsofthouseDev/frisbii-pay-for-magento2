<?php

namespace Radarsofthouse\Reepay\Controller\Standard;

class SetCreditCard extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Customer
     */
    protected $_customerHelper;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $_reepayHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Radarsofthouse\Reepay\Helper\Customer $customerHelper
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Radarsofthouse\Reepay\Helper\Customer $customerHelper,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper
    ) {
        $this->_request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_logger = $logger;
        $this->_customerHelper = $customerHelper;
        $this->_reepayHelper = $reepayHelper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->_request->isPost()) {
            return $this->_resultJsonFactory->create()
                ->setHttpResponseCode(405)
                ->setHeader('Allow', 'POST')
                ->setData([
                    'status' => 'failure',
                    'message' => __('Only POST requests are allowed.')
                ]);
        }

        $params = $this->_request->getParams();
        $this->_logger->addDebug(__METHOD__);

        $result = [];
        $cardId = isset($params['cid']) && is_scalar($params['cid'])
            ? trim((string)$params['cid'])
            : '';

        if ($cardId === '' || ($cardId !== 'new' && !preg_match('/^[A-Za-z0-9_-]+$/D', $cardId))) {
            $result['status'] = 'failure';
            $result['message'] = __('Empty credit card ID.');
            return $this->_resultJsonFactory->create()->setHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0',
                true
            )->setData($result);
        }

        try {
            $quote = $this->_checkoutSession->getQuote();
            if ($cardId !== 'new') {
                if (!$this->_customerSession->isLoggedIn()) {
                    $result['status'] = 'failure';
                    $result['message'] = __('Please log in as a customer.');
                    return $this->_resultJsonFactory->create()->setData($result);
                }

                $customer = $this->_customerSession->getCustomer();
                if ($quote->getCustomerId() && (int)$quote->getCustomerId() !== (int)$customer->getId()) {
                    $result['status'] = 'failure';
                    $result['message'] = __('The selected saved card is not available for this account.');
                    return $this->_resultJsonFactory->create()->setData($result);
                }

                $apiKey = $this->_reepayHelper->getApiKey($quote->getStoreId());
                if (!$this->_customerHelper->isPaymentCardOwnedByCustomer($apiKey, $customer, $cardId)) {
                    $result['status'] = 'failure';
                    $result['message'] = __('The selected saved card is not available for this account.');
                    return $this->_resultJsonFactory->create()->setData($result);
                }
            }

            $quote->setReepayCreditCard($cardId);
            $quote->save();

            $this->_logger->addDebug('Selected saved payment card.', [
                'card_id' => $cardId
            ]);

            $result['status'] = 'success';
            return $this->_resultJsonFactory->create()->setHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0',
                true
            )->setData($result);
        } catch (\Exception $e) {
            $result['status'] = 'failure';
            $result['message'] = $e->getMessage();
            return $this->_resultJsonFactory->create()->setHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0',
                true
            )->setData($result);
        }
    }
}
