<?php

namespace Radarsofthouse\Reepay\Controller\Webhooks;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $logger;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Invoice
     */
    protected $invoiceHelper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    protected $reepayHelper;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $invoice;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var \Magento\Sales\Model\Service\CreditmemoService
     */
    protected $creditmemoService;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory
     */
    protected $transactionSearchResultInterfaceFactory;

    /**
     * @var \Radarsofthouse\Reepay\Model\Status
     */
    protected $reepayStatus;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Charge
     */
    protected $reepayCharge;

    /**
     * @var \Radarsofthouse\Reepay\Helper\SurchargeFee
     */
    protected $reepaySurchargeFee;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Email
     */
    protected $reepayEmail;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * Index constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Radarsofthouse\Reepay\Helper\Invoice $invoiceHelper
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Magento\Sales\Model\Service\CreditmemoService $creditmemoService
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory
     * @param \Radarsofthouse\Reepay\Model\Status $reepayStatus
     * @param \Radarsofthouse\Reepay\Helper\Charge $reepayCharge
     * @param \Radarsofthouse\Reepay\Helper\SurchargeFee $reepaySurchargeFee
     * @param \Radarsofthouse\Reepay\Helper\Email $reepayEmail
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Radarsofthouse\Reepay\Helper\Invoice $invoiceHelper,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory,
        \Radarsofthouse\Reepay\Model\Status $reepayStatus,
        \Radarsofthouse\Reepay\Helper\Charge $reepayCharge,
        \Radarsofthouse\Reepay\Helper\SurchargeFee $reepaySurchargeFee,
        \Radarsofthouse\Reepay\Helper\Email $reepayEmail,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->invoiceHelper = $invoiceHelper;
        $this->order = $order;
        $this->orderService = $orderService;
        $this->orderInterface = $orderInterface;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceService = $invoiceService;
        $this->reepayHelper = $reepayHelper;
        $this->invoice = $invoice;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->transactionSearchResultInterfaceFactory = $transactionSearchResultInterfaceFactory;
        $this->reepayStatus = $reepayStatus;
        $this->reepayCharge = $reepayCharge;
        $this->reepaySurchargeFee = $reepaySurchargeFee;
        $this->reepayEmail = $reepayEmail;
        $this->registry = $registry;
        $this->paymentHelper = $paymentHelper;
        parent::__construct($context);

        // CsrfAwareAction Magento2.3 compatibility
        if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
            $request = $this->getRequest();

            if ($request->isPost() && empty($request->getParam('form_key'))) {
                $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
                $request->setParam('form_key', $formKey->getFormKey());
            }
        }
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $request = $this->getRequest()->getContent();
        $receiveData = json_decode($request, true);

        $this->logger->addDebug(__METHOD__, $receiveData);

        try {
            if (!array_key_exists('event_type', $receiveData)) {
                throw new \Exception('This request event_type not found.', 404);
            }
            switch ($receiveData['event_type']) {
                case 'invoice_refund':
                    if (array_key_exists('subscription', $receiveData)) {
                        $response['status'] = 200;
                        $response['message'] = 'This request is not charge invoice.';
                    } else {
                        sleep(5);
                        $response = $this->refund($receiveData);
                    }
                    $log['response'] = $response;

                    $this->logger->addDebug('Refund response', $log);

                    break;
                case 'invoice_settled':
                    if (array_key_exists('subscription', $receiveData)) {
                        $response['status'] = 200;
                        $response['message'] = 'This request is not charge invoice.';
                    } else {
                        sleep(5);
                        $response = $this->settled($receiveData);
                    }
                    $log['response'] = $response;

                    $this->logger->addDebug('Settled response', $log);

                    break;
                case 'invoice_cancelled':
                    if (array_key_exists('subscription', $receiveData)) {
                        $response['status'] = 200;
                        $response['message'] = 'This request is not charge invoice.';
                    } else {
                        sleep(5);
                        $response = $this->cancel($receiveData);
                    }
                    $log['response'] = $response;

                    $this->logger->addDebug('Cancel response', $log);

                    break;
                case 'invoice_authorized':
                    if (array_key_exists('subscription', $receiveData)) {
                        $response['status'] = 200;
                        $response['message'] = 'This request is not charge invoice.';
                    } else {
                        $response = $this->authorize($receiveData);
                    }
                    $log['response'] = $response;

                    $this->logger->addDebug('Authorized response', $log);

                    break;
                default:
                    $response['status'] = 200;
                    $response['message'] = 'The ' . $receiveData['event_type'] . ' event has been ignored by Magento.';
                    $log['response'] = $response;
                    $this->logger->addDebug('default', $log);

                    break;
            }

            $response['message'] = 'Magento : ' . $response['message'];
            $result = $this->resultJsonFactory->create();
            $result->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
            $result->setHttpResponseCode($response['status']);
            $result->setData($response);

            return  $result;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $log['response_error'] = [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ];
            $this->logger->addError(__METHOD__, $log);

            throw new \Exception($e->getMessage(), (!empty($e->getCode()) ? (int)$e->getCode() : 500));
        } catch (\Exception $e) {
            $log['response_error'] = [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ];
            $this->logger->addError(__METHOD__, $log);

            throw new \Exception($e->getMessage(), (!empty($e->getCode()) ? (int)$e->getCode() : 500));
        }
    }

    /**
     * Capture from Reepay
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\PaymentException
     */
    protected function settled($data)
    {
        $order_id = $data['invoice'];
        $this->logger->addDebug(__METHOD__, [$order_id]);
        $order = $this->orderInterface->loadByIncrementId($order_id);
        $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
        $metadata = $this->invoiceHelper->getMetadata($apiKey, $data['invoice']);
        if (isset($metadata['magento']['module']) && $metadata['magento']['module'] === 'subscription') {
            return [
                'status' => 200,
                'message' => "This order not created from checkout module.",
            ];
        }

        try {
            if (!$order->getId()) {
                $this->logger->addError('The order #' . $order_id . ' no longer exists.');

                return [
                    'status' => 200,
                    'message' => 'Order #' . $order_id . ' does not exist in Magento. It may have been created from another system.'
                ];
            }

            $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
            $reepayTransactionData = $this->invoiceHelper->getTransaction($apiKey, $order_id, $data['transaction']);

            $chargeRes = $this->reepayCharge->get(
                $apiKey,
                $order_id
            );

            $updatePaymentMethod = $this->reepayHelper->getConfig('update_order_payment_method', $order->getStoreId());
            if ($updatePaymentMethod && $order->getPayment() && $order->getPayment()->getMethod() == 'reepay_payment') {
                $sourceTypeAndPaymentMethodMapping = \Radarsofthouse\Reepay\Helper\Data::REEPAY_SOURCE_TYPE_AND_PAYMENT_METHOD_MAPPING;
                if (isset($chargeRes['source']['type'])) {
                    $sourceType = $chargeRes['source']['type'];
                    if (isset($sourceTypeAndPaymentMethodMapping[$sourceType])) {
                        $newMethod = $sourceTypeAndPaymentMethodMapping[$sourceType];
                        $currentMethod = $order->getPayment()->getMethod();

                        if ($currentMethod !== $newMethod) {
                            // save new payment method to order
                            $order->getPayment()->setMethod($newMethod);
                            $methodInstance = $this->paymentHelper->getMethodInstance($newMethod);
                            $methodTitle = $methodInstance ? $methodInstance->getConfigData('title', $order->getStoreId()) : null;
                            $order->getPayment()->setAdditionalInformation('method_title', $methodTitle);
                            $order->save();

                            // Add order history comment
                            $comment = sprintf(
                                'Payment method updated from "%s" to "%s" (%s) by Frisbii webhook.',
                                $currentMethod,
                                $newMethod,
                                $methodTitle ?? 'N/A'
                            );
                            $order->addStatusHistoryComment($comment)
                                ->setIsCustomerNotified(false)
                                ->save();
                        }
                    }
                }
            }

            if (!empty($reepayTransactionData['id']) && $reepayTransactionData['type'] == "settle") {
                // check the transaction has been created
                $transactions = $this->transactionSearchResultInterfaceFactory->create()->addOrderIdFilter($order->getId());
                $hasTxn = false;
                $authorizationTxnId = null;
                foreach ($transactions->getItems() as $transaction) {
                    if ($transaction->getTxnId() == $reepayTransactionData['id']) {
                        $hasTxn = true;
                    }
                    if ($transaction->getTxnType() == \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH) {
                        $authorizationTxnId = $transaction->getTxnId();
                    }
                }

                $reepayMethod = isset($chargeRes['source']['type']) ? $chargeRes['source']['type'] : '';

                $_invoiceType = "";
                $_createInvoice = false;
                $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
                if (
                    $this->reepayHelper->getConfig('auto_capture', $order->getStoreId()) ||
                    $this->reepayHelper->isReepayMethodAutoCapture($paymentMethod, $reepayMethod) ||
                    ($this->reepayHelper->isReepayPaymentMethod($paymentMethod) && $order->getPayment()->getMethodInstance()->isAutoCapture())
                ) {
                    $_invoiceType = 'auto_capture';
                    $_createInvoice = true;
                }

                if (
                    !$_createInvoice &&
                    $this->reepayHelper->getConfig('auto_create_invoice', $order->getStoreId())
                ) {
                    if (
                        isset($chargeRes['state']) &&
                        $chargeRes['state'] == "settled" &&
                        $chargeRes['amount'] == ($order->getGrandTotal() * 100)
                    ) {
                        $_invoiceType = 'settled_in_reepay';
                        $_createInvoice = true;
                    }
                }

                $this->registry->register('is_reepay_settled_webhook', 1);

                if ($hasTxn) {
                    if ($_createInvoice && $order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($order);
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $invoice->getOrder()->setCustomerNoteNotify(false);
                        $invoice->getOrder()->setIsInProcess(true);
                        $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
                        $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                        $transactionSave->save();

                        $this->logger->addDebug("#1 : Automatic create invoice for the order #" . $order_id . " : Invoice type => " . $_invoiceType);
                    }

                    $this->logger->addDebug("Magento have created the transaction '" . $reepayTransactionData['id'] . "' already.");

                    return [
                        'status' => 200,
                        'message' => "Magento have created the transaction '" . $reepayTransactionData['id'] . "' already.",
                    ];
                }

                $transactionID = $this->reepayHelper->addCaptureTransactionToOrder($order, $reepayTransactionData, $chargeRes, $authorizationTxnId);
                if ($transactionID) {
                    $this->reepayHelper->setReepayPaymentState($order->getPayment(), 'settled');
                    $order->save();

                    $this->surchargeFee($order_id, $chargeRes);

                    if ($_createInvoice && $order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($order);
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $invoice->getOrder()->setCustomerNoteNotify(false);
                        $invoice->getOrder()->setIsInProcess(true);
                        $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
                        $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                        $transactionSave->save();

                        $this->logger->addDebug("#2 : Automatic create invoice for the order #" . $order_id . " : Invoice type => " . $_invoiceType);
                    }

                    $this->logger->addDebug('Settled order #' . $order_id . " , transaction ID : " . $transactionID);

                    return [
                        'status' => 200,
                        'message' => 'Settled order #' . $order_id . " , transaction ID : " . $transactionID,
                    ];
                } else {
                    $this->logger->addError('Cannot create capture transaction for order #' . $order_id . " , transaction : " . $reepayTransactionData['id']);

                    return [
                        'status' => 500,
                        'message' => 'Cannot create capture transaction for order #' . $order_id . " , transaction : " . $reepayTransactionData['id'],
                    ];
                }
            } else {
                $this->logger->addError('Cannot get transaction data from Frisbii : transaction ID = ' . $data['transaction']);

                return [
                    'status' => 500,
                    'message' => 'Cannot get transaction data from Frisbii : transaction ID = ' . $data['transaction']
                ];
            }
        } catch (\Exception $e) {
            $this->logger->addError('settled webhook exception : ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'settled webhook exception : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Refund from Reepay
     *
     * @param array $data
     * @return void | array error message
     */
    protected function refund($data)
    {
        $order_id = $data['invoice'];
        $this->logger->addDebug(__METHOD__, [$order_id]);
        $order = $this->orderInterface->loadByIncrementId($order_id);
        $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
        $metadata = $this->invoiceHelper->getMetadata($apiKey, $data['invoice']);
        if (isset($metadata['magento']['module']) && $metadata['magento']['module'] === 'subscription') {
            return [
                'status' => 200,
                'message' => "This order not created from checkout module.",
            ];
        }

        try {
            if (!$order->getId()) {
                $this->logger->addError('The order #' . $order_id . ' no longer exists.');

                return [
                    'status' => 200,
                    'message' => 'Order #' . $order_id . ' does not exist in Magento. It may have been created from another system.'
                ];
            }

            $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
            $refundData = $this->invoiceHelper->getTransaction($apiKey, $order_id, $data['transaction']);

            if (!empty($refundData['id']) && $refundData['state'] == "refunded") {

                // check the transaction has been created
                $transactions = $this->transactionSearchResultInterfaceFactory->create()->addOrderIdFilter($order->getId());
                $hasTxn = false;
                foreach ($transactions->getItems() as $transaction) {
                    if ($transaction->getTxnId() == $refundData['id']) {
                        $hasTxn = true;
                    }
                }

                if ($hasTxn) {
                    $this->logger->addDebug("Magento have created the transaction '" . $refundData['id'] . "' already.");
                    return [
                        'status' => 200,
                        'message' => "Magento have created the transaction '" . $refundData['id'] . "' already.",
                    ];
                }

                // create refund transaction
                $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
                $chargeRes = $this->reepayCharge->get(
                    $apiKey,
                    $order_id
                );

                //                $refundAmount = $this->reepayHelper->convertAmount($refundData['amount']);
                $transactionID = $this->reepayHelper->addRefundTransactionToOrder($order, $refundData, $chargeRes);

                if ($transactionID) {
                    $this->reepayHelper->setReepayPaymentState($order->getPayment(), 'refunded');
                    $order->save();

                    $this->logger->addDebug('Refunded order #' . $order_id . " , transaction ID : " . $transactionID);

                    return [
                        'status' => 200,
                        'message' => 'Refunded order #' . $order_id . " , transaction ID : " . $transactionID,
                    ];
                } else {
                    $this->logger->addError('Cannot create refund transaction for order #' . $order_id . " , transaction : " . $refundData['id']);

                    return [
                        'status' => 500,
                        'message' => 'Cannot create refund transaction for order #' . $order_id . " , transaction : " . $refundData['id'],
                    ];
                }
            } else {
                $this->logger->addError('Cannot get refund transaction data from Frisbii : transaction ID = ' . $data['transaction']);

                return [
                    'status' => 500,
                    'message' => 'Cannot get refund transaction data from Frisbii : transaction ID = ' . $data['transaction'],
                ];
            }
        } catch (\Exception $e) {
            $this->logger->addError('refund webhook exception : ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'refund webhook exception : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel from Reepay
     *
     * @param array $data
     * @return array
     */
    protected function cancel($data)
    {
        $order_id = $data['invoice'];
        $this->logger->addDebug(__METHOD__, [$order_id]);
        $order = $this->orderInterface->loadByIncrementId($order_id);
        $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
        $metadata = $this->invoiceHelper->getMetadata($apiKey, $data['invoice']);
        if (isset($metadata['magento']['module']) && $metadata['magento']['module'] === 'subscription') {
            return [
                'status' => 200,
                'message' => "This order not created from checkout module.",
            ];
        }

        try {
            if (!$order->getId()) {
                $this->logger->addError('The order #' . $order_id . ' no longer exists.');

                return [
                    'status' => 200,
                    'message' => 'Order #' . $order_id . ' does not exist in Magento. It may have been created from another system.'
                ];
            }

            if (!$order->canCancel()) {
                $this->logger->addError('Cannot cancel this order');

                if ($order->getState() == \Magento\Sales\Model\Order::STATE_CANCELED) {
                    return [
                        'status' => 200,
                        'message' => 'The order was cancelled in Magento'
                    ];
                }

                return [
                    'status' => 500,
                    'message' => 'Cannot cancel this order'
                ];
            }

            $order->cancel();
            $order->addStatusHistoryComment('Frisbii : order have been cancelled by the webhook');
            $order->save();

            $_payment = $order->getPayment();
            $this->reepayHelper->setReepayPaymentState($_payment, 'cancelled');
            $order->save();

            $this->logger->addDebug('cancelled order #' . $order_id);

            return [
                'status' => 200,
                'message' => 'cancelled order #' . $order_id
            ];
        } catch (\Exception $e) {
            $this->logger->addError('cancel webhook exception : ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'cancel webhook exception : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create authorize transaction if have no the transaction
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\PaymentException
     */
    protected function authorize($data)
    {
        $order_id = $data['invoice'];
        $this->logger->addDebug(__METHOD__, [$order_id]);
        $order = $this->orderInterface->loadByIncrementId($order_id);
        $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
        $metadata = $this->invoiceHelper->getMetadata($apiKey, $data['invoice']);
        if (isset($metadata['magento']['module']) && $metadata['magento']['module'] === 'subscription') {
            return [
                'status' => 200,
                'message' => "This order not created from checkout module.",
            ];
        }

        try {
            if (!$order->getId()) {
                $this->logger->addError('The order #' . $order_id . ' no longer exists.');

                return [
                    'status' => 200,
                    'message' => 'Order #' . $order_id . ' does not exist in Magento. It may have been created from another system.'
                ];
            }

            // check if has reepay status row for the order, That means the order has been authorized
            $reepayStatus = $this->reepayStatus->load($order_id, 'order_id');
            if ($reepayStatus->getStatusId()) {
                $this->logger->addDebug('order #' . $order_id . ' has been authorized already');

                return [
                    'status' => 200,
                    'message' => 'order #' . $order_id . ' has been authorized already',
                ];
            }

            $apiKey = $this->reepayHelper->getApiKey($order->getStoreId());
            $chargeRes = $this->reepayCharge->get(
                $apiKey,
                $order_id
            );

            $updatePaymentMethod = $this->reepayHelper->getConfig('update_order_payment_method', $order->getStoreId());
            if ($updatePaymentMethod && $order->getPayment() && $order->getPayment()->getMethod() == 'reepay_payment') {
                $sourceTypeAndPaymentMethodMapping = \Radarsofthouse\Reepay\Helper\Data::REEPAY_SOURCE_TYPE_AND_PAYMENT_METHOD_MAPPING;
                if (isset($chargeRes['source']['type'])) {
                    $sourceType = $chargeRes['source']['type'];
                    if (isset($sourceTypeAndPaymentMethodMapping[$sourceType])) {
                        $newMethod = $sourceTypeAndPaymentMethodMapping[$sourceType];
                        $currentMethod = $order->getPayment()->getMethod();

                        if ($currentMethod !== $newMethod) {
                            // save new payment method to order
                            $order->getPayment()->setMethod($newMethod);
                            $methodInstance = $this->paymentHelper->getMethodInstance($newMethod);
                            $methodTitle = $methodInstance ? $methodInstance->getConfigData('title', $order->getStoreId()) : null;
                            $order->getPayment()->setAdditionalInformation('method_title', $methodTitle);
                            $order->save();

                            // Add order history comment
                            $comment = sprintf(
                                'Payment method updated from "%s" to "%s" (%s) by Frisbii webhook.',
                                $currentMethod,
                                $newMethod,
                                $methodTitle ?? 'N/A'
                            );
                            $order->addStatusHistoryComment($comment)
                                ->setIsCustomerNotified(false)
                                ->save();
                        }
                    }
                }
            }

            // add Reepay payment data
            $data = [
                'order_id' => $order_id,
                'first_name' => $order->getBillingAddress()->getFirstname(),
                'last_name' => $order->getBillingAddress()->getLastname(),
                'email' => $order->getCustomerEmail(),
                // 'token' => $params['id'],
                'masked_card_number' => isset($chargeRes['source']['masked_card']) ? $chargeRes['source']['masked_card'] : '',
                'fingerprint' => isset($chargeRes['source']['fingerprint']) ? $chargeRes['source']['fingerprint'] : '',
                'card_type' => isset($chargeRes['source']['card_type']) ? $chargeRes['source']['card_type'] : '',
                'status' => $chargeRes['state'],
            ];

            $newReepayStatus = $this->reepayStatus;
            $newReepayStatus->setData($data);
            $newReepayStatus->save();
            $this->logger->addDebug('save reepay status');

            $this->reepayHelper->addTransactionToOrder($order, $chargeRes);
            $this->logger->addDebug('order #' . $order_id . ' has been authorized by the webhook');

            $this->surchargeFee($order_id, $chargeRes);
            return [
                'status' => 200,
                'message' => 'order #' . $order_id . ' has been authorized by the webhook',
            ];
        } catch (\Exception $e) {
            $this->logger->addError('webhook authorize exception : ' . $e->getMessage());

            return [
                'status' => 500,
                'message' => 'webhook authorize error : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Surcharge Fee
     *
     * @param string $orderIncrementId
     * @param array $chargeRes
     */
    private function surchargeFee($orderIncrementId, $chargeRes)
    {
        $order = $this->orderInterface->loadByIncrementId($orderIncrementId);
        $storeId = $order->getStoreId();
        $isSurchargeFeeEnable = $this->reepayHelper->isSurchargeFeeEnabled($storeId);
        $this->logger->addDebug(__METHOD__, ['isSurchargeFeeEnable' => $isSurchargeFeeEnable, 'orderIncrementId' => $orderIncrementId]);
        if ($isSurchargeFeeEnable) {
            //to test add 50.00
            //            $chargeRes['source']['surcharge_fee'] = '5100';
            $this->logger->addDebug('updateFeeToOrder', $chargeRes);
            $this->reepaySurchargeFee->updateFeeToOrder($orderIncrementId, $chargeRes);
        } else {
            $this->logger->addDebug('NotupdateFeeToOrder', $chargeRes);
            $this->reepayEmail->sendEmail($orderIncrementId);
        }
    }
}
