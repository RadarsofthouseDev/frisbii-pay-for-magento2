<?php

namespace Radarsofthouse\Reepay\Model\Invoice\Total;

class SurchargeFee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
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
     * @param \Radarsofthouse\Reepay\Helper\Logger $helperLogger
     * @param array $data
     */
    public function __construct(
        \Radarsofthouse\Reepay\Helper\Data $helperData,
        \Radarsofthouse\Reepay\Helper\SurchargeFee $helperSurchargeFee,
        \Radarsofthouse\Reepay\Helper\Logger $helperLogger,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_helperData = $helperData;
        $this->_helperSurchargeFee = $helperSurchargeFee;
        $this->_helperLogger = $helperLogger;
    }

    /**
     * Collect total
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $payment = $invoice->getOrder()->getPayment();
        $storeId = $invoice->getOrder()->getStoreId();
        $paymentMethod = null;
        if ($payment !== null) {
            $paymentMethod = $payment->getMethod();
        }

        $isReepayPaymentMethod = $this->_helperData->isReepayPaymentMethod($paymentMethod);
        $isEnable = $this->_helperData->isSurchargeFeeEnabled($storeId);
        if (!$isEnable || !$isReepayPaymentMethod) {
            return $this;
        }
        $invoice->setReepaySurchargeFee(0.00);

        if ($this->_helperSurchargeFee->isInvoicedSurchargeFee($invoice->getOrder()->getId())) {
            return $this;
        }

        $surchargeFee = $invoice->getOrder()->getReepaySurchargeFee();
        $invoice->setReepaySurchargeFee($surchargeFee);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $surchargeFee);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $surchargeFee);
        if ((float)$surchargeFee > 0) {
            $this->_helperLogger->addDebug('Applied surcharge fee to invoice.', [
                'surcharge_fee' => $surchargeFee
            ]);
        }

        return $this;
    }
}
