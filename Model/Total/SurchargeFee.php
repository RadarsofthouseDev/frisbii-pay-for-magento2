<?php

namespace Radarsofthouse\Reepay\Model\Total;

use Magento\Framework\App\ProductMetadataInterface;

class SurchargeFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Radarsofthouse\Reepay\Helper\Data
     */
    private $_helperData;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    private $_helperLogger;

    /**
     * Constructor
     *
     * @param \Radarsofthouse\Reepay\Helper\Data $helperData
     * @param \Radarsofthouse\Reepay\Helper\Logger $helperLogger
     */
    public function __construct(
        \Radarsofthouse\Reepay\Helper\Data $helperData,
        \Radarsofthouse\Reepay\Helper\Logger $helperLogger
    ) {
        $this->_helperData = $helperData;
        $this->_helperLogger = $helperLogger;
    }

    /**
     * Collect total
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (!count($shippingAssignment->getItems())) {
            return $this;
        }
        $paymentMethod = $quote->getPayment()->getMethod();
        $storeId = $quote->getStoreId();

        $isReepayPaymentMethod = $this->_helperData->isReepayPaymentMethod($paymentMethod);
        $isEnable = $this->_helperData->isSurchargeFeeEnabled($storeId);
        if ($isEnable && $isReepayPaymentMethod) {
            $surchargeFee = $quote->getReepaySurchargeFee();
            $total->setTotalAmount('reepay_surcharge_fee', $surchargeFee);
            $total->setBaseTotalAmount('reepay_surcharge_fee', $surchargeFee);
            $total->setReepaySurchargeFee($surchargeFee);
            $total->setGrandTotal($total->getGrandTotal() + $surchargeFee);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $surchargeFee);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata = $objectManager->get(ProductMetadataInterface::class);
            $version = (float)$productMetadata->getVersion();
            if ($version <= 2.1) {
                $total->setGrandTotal($total->getGrandTotal() + $surchargeFee);
            }
        }
        return $this;
    }

    /**
     * Fetch
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $paymentMethod = $quote->getPayment()->getMethod();
        $storeId = $quote->getStoreId();
        $isReepayPaymentMethod = $this->_helperData->isReepayPaymentMethod($paymentMethod);
        $isEnable = $this->_helperData->isSurchargeFeeEnabled($storeId);
        if ($isEnable && $isReepayPaymentMethod) {
            $surchargeFee = $quote->getReepaySurchargeFee();
            return [
                'code' => 'reepay_surcharge_fee',
                'title' => __('Surcharge Fee'),
                'value' => $surchargeFee
            ];
        }
        return [];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Surcharge Fee');
    }

    /**
     * Clear values
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
    protected function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
}
