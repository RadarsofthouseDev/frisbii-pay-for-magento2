<?php

declare(strict_types=1);

namespace Radarsofthouse\Reepay\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutFactory;

class FrisbiiSettings extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ModuleList
     */
    protected $moduleList;

    /**
     * Constructor
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param LayoutFactory $layoutFactory
     * @param ModuleList $moduleList
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        LayoutFactory $layoutFactory,
        ModuleList $moduleList
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->layoutFactory = $layoutFactory;
        $this->moduleList = $moduleList;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->modifySettingTab($meta);
        $meta = $this->modifyAgeVerificationEnabled($meta);
        $meta = $this->modifyMinimumUserAge($meta);
        return $meta;
    }

    /**
     * Modify Frisbii Settings tab
     *
     * @param array $meta
     * @return array
     */
    protected function modifySettingTab(array $meta): array
    {
        $fieldPath = $this->arrayManager->findPath('frisbii-settings', $meta);
        if ($fieldPath) {
            $meta = $this->arrayManager->merge(
                $fieldPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'sortOrder' => 11
                ]
            );
        }
        return $meta;
    }

    /**
     * Modify Age Verification Enabled field
     *
     * @param array $meta
     * @return array
     */
    protected function modifyAgeVerificationEnabled(array $meta): array
    {
        $fieldPath = $this->arrayManager->findPath('frisbii_age_verification_enabled', $meta, null, 'children');
        if ($fieldPath) {
            $meta = $this->arrayManager->merge(
                $fieldPath . static::META_CONFIG_PATH,
                $meta,
                []
            );
        }
        return $meta;
    }

    /**
     * Modify Minimum User Age field
     *
     * @param array $meta
     * @return array
     */
    protected function modifyMinimumUserAge(array $meta): array
    {
        $fieldPath = $this->arrayManager->findPath('frisbii_minimum_user_age', $meta, null, 'children');
        if ($fieldPath) {
            $meta = $this->arrayManager->merge(
                $fieldPath . static::META_CONFIG_PATH,
                $meta,
                []
            );
        }
        return $meta;
    }
}
