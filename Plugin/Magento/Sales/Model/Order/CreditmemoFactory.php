<?php

namespace Radarsofthouse\Reepay\Plugin\Magento\Sales\Model\Order;

class CreditmemoFactory
{
    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    private $_helperData;

    /**
     * @var \Radarsofthouse\Reepay\Helper\SurchargeFee
     */
    private $_helperSurchargeFee;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    private $_helperLogger;

    /**
     * Constructor
     *
     * @param \Radarsofthouse\Reepay\Helper\Data $helperData
     * @param \Radarsofthouse\Reepay\Helper\SurchargeFee $helperSurchargeFee
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     */
    public function __construct(
        \Radarsofthouse\Reepay\Helper\Data $helperData,
        \Radarsofthouse\Reepay\Helper\SurchargeFee $helperSurchargeFee,
        \Radarsofthouse\Reepay\Helper\Logger $logger
    ) {
        $this->_helperData = $helperData;
        $this->_helperSurchargeFee = $helperSurchargeFee;
        $this->_helperLogger = $logger;
    }

    /**
     * Around Create By Order
     *
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order $order
     * @param array $data
     * @return \Magento\Sales\Model\Order\Creditmemo $result
     */
    public function aroundCreateByOrder(
        \Magento\Sales\Model\Order\CreditmemoFactory $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order $order,
        $data = []
    ) {
        /** @var \Magento\Sales\Model\Order\Creditmemo $result */
        $result = $proceed($order, $data);
        $payment = $order->getPayment();
        $storeId = $order->getStoreId();
        $paymentMethod = null;
        if ($payment !== null) {
            $paymentMethod = $payment->getMethod();
        }
        $isReepayPaymentMethod = $this->_helperData->isReepayPaymentMethod($paymentMethod);
        $isEnable = $this->_helperData->isSurchargeFeeEnabled($storeId);
        if (!$isEnable || !$isReepayPaymentMethod) {
            return $result;
        }
        if (array_key_exists('reepay_surcharge_fee', $data)) {
            $surchargeFee = round((float)$result->getReepaySurchargeFee(), 2);
            $inputSurchargeFee = round((float)$data['reepay_surcharge_fee'], 2);
            $availableSurchargeFee = $this->_helperSurchargeFee->getAvailableSurchargeFeeRefundAmount($order->getId());
            if ($surchargeFee === $inputSurchargeFee) {
                return $result;
            }
            if ($inputSurchargeFee > $availableSurchargeFee) {
                $inputSurchargeFee = $availableSurchargeFee;
            }
            $this->_helperLogger->addDebug(
                __METHOD__,
                [
                    'surchargeFee' => $surchargeFee,
                    'inputSurchargeFee' => $inputSurchargeFee,
                    'GrandTotal' => $result->getGrandTotal(),
                    'BaseGrandTotal' => $result->getBaseGrandTotal()
                ]
            );
            $result->setGrandTotal($result->getGrandTotal() - $surchargeFee + $inputSurchargeFee);
            $result->setBaseGrandTotal($result->getBaseGrandTotal() - $surchargeFee + $inputSurchargeFee);
            $result->setReepaySurchargeFee($inputSurchargeFee);
            $this->_helperLogger->addDebug(
                __METHOD__,
                [
                    'newGrandTotal' => $result->getGrandTotal(),
                    'newBaseGrandTotal' => $result->getBaseGrandTotal()
                ]
            );
        }
        return $result;
    }

    /**
     * Around Create By Order
     *
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param array $data
     * @return \Magento\Sales\Model\Order\Creditmemo $result
     */
    public function aroundCreateByInvoice(
        \Magento\Sales\Model\Order\CreditmemoFactory $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Invoice $invoice,
        $data = []
    ) {
        /** @var \Magento\Sales\Model\Order\Creditmemo $result */
        $result = $proceed($invoice, $data);
        $order = $invoice->getOrder();
        $payment = $order->getPayment();
        $storeId = $order->getStoreId();
        $paymentMethod = null;
        if ($payment !== null) {
            $paymentMethod = $payment->getMethod();
        }
        $isReepayPaymentMethod = $this->_helperData->isReepayPaymentMethod($paymentMethod);
        $isEnable = $this->_helperData->isSurchargeFeeEnabled($storeId);
        if (!$isEnable || !$isReepayPaymentMethod) {
            return $result;
        }
        if (array_key_exists('reepay_surcharge_fee', $data)) {
            $surchargeFee = round((float)$result->getReepaySurchargeFee(), 2);
            $inputSurchargeFee = round((float)$data['reepay_surcharge_fee'], 2);
            $availableSurchargeFee = $this->_helperSurchargeFee->getAvailableSurchargeFeeRefundAmount(
                $invoice->getOrder()->getId()
            );
            if ($surchargeFee === $inputSurchargeFee) {
                return $result;
            }
            if ($inputSurchargeFee > $availableSurchargeFee) {
                $inputSurchargeFee = $availableSurchargeFee;
            }
            $this->_helperLogger->addDebug(
                __METHOD__,
                [
                    'surchargeFee' => $surchargeFee,
                    'inputSurchargeFee' => $inputSurchargeFee,
                    'GrandTotal' => $result->getGrandTotal(),
                    'BaseGrandTotal' => $result->getBaseGrandTotal()
                ]
            );
            $result->setGrandTotal($result->getGrandTotal() - $surchargeFee + $inputSurchargeFee);
            $result->setBaseGrandTotal($result->getBaseGrandTotal() - $surchargeFee + $inputSurchargeFee);
            $result->setReepaySurchargeFee($inputSurchargeFee);
            $this->_helperLogger->addDebug(
                __METHOD__,
                [
                    'newGrandTotal'=> $result->getGrandTotal(),
                    'newBaseGrandTotal'=> $result->getBaseGrandTotal()
                ]
            );
        }
        return $result;
    }
}
