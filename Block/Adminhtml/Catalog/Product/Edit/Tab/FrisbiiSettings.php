<?php

declare(strict_types=1);

namespace Radarsofthouse\Reepay\Block\Adminhtml\Catalog\Product\Edit\Tab;

class FrisbiiSettings extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /** Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get table label.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel(): \Magento\Framework\Phrase|string
    {
        return __('Frisbii Settings');
    }

    /**
     * Get tabel title.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle(): \Magento\Framework\Phrase|string
    {
        return __('Frisbii Settings');
    }

    /**
     * Always show tab.
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Always hidden.
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
