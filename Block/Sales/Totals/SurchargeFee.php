<?php

namespace Radarsofthouse\Reepay\Block\Sales\Totals;

class SurchargeFee extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    private $_helperData;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    private $_helperLogger;

    /**
     * SurchargeFee constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Radarsofthouse\Reepay\Helper\Data $helperData
     * @param \Radarsofthouse\Reepay\Helper\Logger $helperLogger
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Radarsofthouse\Reepay\Helper\Data $helperData,
        \Radarsofthouse\Reepay\Helper\Logger $helperLogger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helperData = $helperData;
        $this->_helperLogger = $helperLogger;
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get Label Properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get Value Properties
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Init Totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $payment */
        $payment = $this->_order->getPayment();
        $storeId = $this->_order->getStoreId();
        $paymentMethod = null;
        if ($payment !== null) {
            $paymentMethod = $payment->getMethod();
        }

        $isReepayPaymentMethod = $this->_helperData->isReepayPaymentMethod($paymentMethod);
        $isEnable = $this->_helperData->isSurchargeFeeEnabled($storeId);
        $surchargeFeeAmount = (float)$this->getSource()->getReepaySurchargeFee();

        if ($surchargeFeeAmount == 0) {
            if (!$isEnable || !$isReepayPaymentMethod) {
                return $this;
            }
        }

        $surchargeFeeTotal = new \Magento\Framework\DataObject(
            [
                'code' => 'reepay_surcharge_fee',
                'strong' => false,
                'value' => $surchargeFeeAmount,
                'label' => __('Surcharge Fee'),
            ]
        );
        if ($surchargeFeeAmount > 0) {
            $this->_helperLogger->addDebug('Displayed surcharge fee total.', [
                'order_id' => $this->_order->getId(),
                'order_increment_id' => $this->_order->getIncrementId(),
                'surcharge_fee' => $surchargeFeeAmount
            ]);
        }

        //        $parent->addTotal($surchargeFee, 'reepay_surcharge_fee');
        $this->getParentBlock()->addTotal($surchargeFeeTotal, 'reepay_surcharge_fee');

        return $this;
    }
}
