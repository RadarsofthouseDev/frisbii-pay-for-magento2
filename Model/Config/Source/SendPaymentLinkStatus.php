<?php

namespace Radarsofthouse\Reepay\Model\Config\Source;

class SendPaymentLinkStatus implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;
    /**
     * @var Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * Constructor
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
    ) {
        $this->orderConfig = $orderConfig;
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * Return order status
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => '0', 'label' => __('Do not change status')]
        ];

        /** @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection */
        $statusCollection = $this->statusCollectionFactory->create();
        $statusLabels = [];
        /** @var \Magento\Sales\Model\Order\Status $status */
        foreach ($statusCollection as $status) {
            $statusLabels[$status->getStatus()] = $status->getLabel();
        }

        // Get all states
        $orderStates = $this->orderConfig->getStates();
        foreach ($orderStates as $state => $stateLabel) {
            $statuses = $this->orderConfig->getStateStatuses($state);
            foreach ($statuses as $key => $label) {
                $options[] = [
                    'value' => $key,
                    'label' => isset($statusLabels[$key]) ? $statusLabels[$key] : ucfirst($label),
                ];
            }
        }

        return $options;
    }
}
