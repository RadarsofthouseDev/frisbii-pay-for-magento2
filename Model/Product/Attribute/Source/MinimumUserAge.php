<?php

declare(strict_types=1);

namespace Radarsofthouse\Reepay\Model\Product\Attribute\Source;

class MinimumUserAge extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $this->_options = [
            ['value' => '15', 'label' => __('15 years')],
            ['value' => '16', 'label' => __('16 years')],
            ['value' => '18', 'label' => __('18 years')],
            ['value' => '21', 'label' => __('21 years')]
        ];
        return $this->_options;
    }

    /**
     * @return array
     */
    public function getFlatColumns(): array
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => $attributeCode . ' column',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getFlatIndexes(): array
    {
        $indexes = [];

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = ['type' => 'index', 'fields' => [$this->getAttribute()->getAttributeCode()]];

        return $indexes;
    }

    /**
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->eavAttrEntity->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
