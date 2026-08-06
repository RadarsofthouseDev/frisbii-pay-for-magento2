<?php

declare(strict_types=1);

namespace Radarsofthouse\Reepay\Plugin;

use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Zend_Db_Expr;

class SalesOrderGridPlugin
{
    const PAYMENT_ALIAS = 'reepay_order_payment';
    const FIELD_NAME = 'age_verification_result';

    /**
     * Add the age verification result to the sales order grid query.
     *
     * @param Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $subject, bool $printQuery = false, bool $logQuery = false): array
    {
        if (!$subject->isLoaded()) {
            $this->addAgeVerificationResult($subject);
        }

        return [$printQuery, $logQuery];
    }

    /**
     * Add the age verification result to sales order grid exports.
     *
     * @param CollectionFactory $subject
     * @param \Magento\Framework\Data\Collection $result
     * @param string|null $requestName
     * @return \Magento\Framework\Data\Collection
     */
    public function afterGetReport(
        CollectionFactory $subject,
        \Magento\Framework\Data\Collection $result,
        $requestName = null
    ): \Magento\Framework\Data\Collection {
        if ($requestName === 'sales_order_grid_data_source' && $result instanceof Collection) {
            $this->addAgeVerificationResult($result);
        }

        return $result;
    }

    /**
     * Add the age verification result field to the collection select.
     *
     * @param Collection $collection
     * @return void
     */
    private function addAgeVerificationResult(Collection $collection)
    {
        $select = $collection->getSelect();
        $from = $select->getPart(Select::FROM);
        if (isset($from[self::PAYMENT_ALIAS])) {
            return;
        }

        $paymentTable = $collection->getResource()->getTable('sales_order_payment');
        $expression = $this->getAgeVerificationExpression();
        $select->joinLeft(
            [self::PAYMENT_ALIAS => $paymentTable],
            self::PAYMENT_ALIAS . '.parent_id = main_table.entity_id',
            [self::FIELD_NAME => $expression]
        );
        // Magento's addFilterToMap() supports Zend_Db_Expr despite its string-only PHPDoc.
        // @phpstan-ignore argument.type
        $collection->addFilterToMap(self::FIELD_NAME, $expression);
    }

    /**
     * Build the SQL expression for the stored age verification result.
     *
     * @return Zend_Db_Expr
     */
    private function getAgeVerificationExpression(): Zend_Db_Expr
    {
        return new Zend_Db_Expr(
            'IF(JSON_VALID(' . self::PAYMENT_ALIAS . '.additional_information), '
            . 'JSON_UNQUOTE(JSON_EXTRACT('
            . self::PAYMENT_ALIAS
            . ".additional_information, '$.age_verification_result')), NULL)"
        );
    }
}
