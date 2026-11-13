<?php

namespace Radarsofthouse\Reepay\Setup\Patch\Data;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EncryptApiKeys implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EncryptorInterface $encryptor
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->encryptor = $encryptor;
    }

    /**
     * Encrypt legacy plaintext API keys in Magento configuration storage.
     *
     * @return void
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $configTable = $this->moduleDataSetup->getTable('core_config_data');
        $select = $connection->select()
            ->from($configTable, ['config_id', 'value'])
            ->where('path IN (?)', [
                'payment/reepay_payment/private_key_test',
                'payment/reepay_payment/private_key',
            ]);

        $connection->startSetup();
        foreach ($connection->fetchAll($select) as $config) {
            $value = trim((string)$config['value']);
            if ($value === '' || preg_match('/^\d+:\d+:/', $value)) {
                continue;
            }

            $connection->update(
                $configTable,
                ['value' => $this->encryptor->encrypt($value)],
                ['config_id = ?' => (int)$config['config_id']]
            );
        }
        $connection->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
