<?php

namespace Radarsofthouse\Reepay\Controller\Standard;

class RemoveCard extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Customer
     */
    protected $_customerHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $_reepayHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Radarsofthouse\Reepay\Helper\Customer $customerHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Radarsofthouse\Reepay\Helper\Customer $customerHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_request = $request;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_logger = $logger;
        $this->_customerHelper = $customerHelper;
        $this->_customerSession = $customerSession;
        $this->_reepayHelper = $reepayHelper;
        $this->_storeManager = $storeManager;
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

        if (empty($params['cid'])) {
            $result['status'] = 'failure';
            $result['message'] = __('Empty credit card ID.');
            return $this->_resultJsonFactory->create()
                ->setHttpResponseCode(400)
                ->setHeader(
                    'Cache-Control',
                    'no-store, no-cache, must-revalidate, max-age=0',
                    true
                )->setData($result);
        }

        if ($this->_customerSession->isLoggedIn()) {
            $costomer_id = $this->_customerSession->getCustomer()->getId();
            $apiKey = $this->_reepayHelper->getApiKey($this->_storeManager->getStore()->getId());

            $deleteResult = $this->_customerHelper->deletePaymentCardByCustomerId(
                $apiKey,
                $costomer_id,
                $params['cid']
            );

            if ($deleteResult) {
                $this->_logger->addDebug('Deleted saved payment card.', [
                    'card_id' => $params['cid']
                ]);
                $result['status'] = 'success';
                return $this->_resultJsonFactory->create()->setHeader(
                    'Cache-Control',
                    'no-store, no-cache, must-revalidate, max-age=0',
                    true
                )->setData($result);
            } else {
                $result['status'] = 'failure';
                $result['message'] = __('Cannot delete your card. Please try again later.');
                return $this->_resultJsonFactory->create()->setHeader(
                    'Cache-Control',
                    'no-store, no-cache, must-revalidate, max-age=0',
                    true
                )->setData($result);
            }
        } else {
            $result['status'] = 'failure';
            $result['message'] = __('Please log in as a customer.');
            return $this->_resultJsonFactory->create()
                ->setHttpResponseCode(401)
                ->setHeader(
                    'Cache-Control',
                    'no-store, no-cache, must-revalidate, max-age=0',
                    true
                )->setData($result);
        }
    }
}
