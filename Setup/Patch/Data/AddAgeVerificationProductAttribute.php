<?php

declare(strict_types=1);

namespace Radarsofthouse\Reepay\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddAgeVerificationProductAttribute implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {}

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'frisbii_age_verification_enabled')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'frisbii_age_verification_enabled',
                [
                    'type' => 'int',
                    'label' => 'Enable Age Verification',
                    'input' => 'boolean',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Frisbii Settings',
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => ['values' => [""]]
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'frisbii_minimum_user_age')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'frisbii_minimum_user_age',
                [
                    'type' => 'int',
                    'label' => 'Minimum user age',
                    'input' => 'select',
                    'source' => \Radarsofthouse\Reepay\Model\Product\Attribute\Source\MinimumUserAge::class,
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => '31',
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Frisbii Settings',
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => ''
                ]
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        // Skip removing attribute if Radarsofthouse_BillwerkPlusSubscription module is enabled
        $moduleList = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Module\ModuleList::class);
        if (!$moduleList->has('Radarsofthouse_BillwerkPlusSubscription') || !(($moduleInfo = $moduleList->getOne('Radarsofthouse_BillwerkPlusSubscription')) &&
            !empty($moduleInfo['setup_version']) &&
            version_compare($moduleInfo['setup_version'], '1.0.18', '>'))) {
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'frisbii_age_verification_enabled');
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'frisbii_minimum_user_age');
        } elseif (
            $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'frisbii_minimum_user_age') && ($moduleInfo = $moduleList->getOne('Radarsofthouse_BillwerkPlusSubscription')) && !empty($moduleInfo['setup_version']) &&
            version_compare($moduleInfo['setup_version'], '1.0.18', '>') && class_exists('Radarsofthouse\BillwerkPlusSubscription\Model\Config\Source\MinimumUserAge')
        ) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'frisbii_minimum_user_age',
                'source',
                \Radarsofthouse\BillwerkPlusSubscription\Model\Config\Source\MinimumUserAge::class
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
