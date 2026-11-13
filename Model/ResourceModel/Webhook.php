<?php

namespace Radarsofthouse\Reepay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Db_Expr;

class Webhook extends AbstractDb
{
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSED = 'processed';
    const CLAIM_NEW = 'new';
    const CLAIM_PROCESSED = 'processed';
    const CLAIM_BUSY = 'busy';
    const PROCESSING_LEASE = 600;

    protected function _construct()
    {
        $this->_init('radarsofthouse_reepay_webhook', 'webhook_id');
    }

    /**
     * Claim a webhook ID before dispatching its event.
     *
     * @param string $webhookId
     * @param string $eventId
     * @param string $eventType
     * @return string
     */
    public function claim($webhookId, $eventId, $eventType)
    {
        $now = gmdate('Y-m-d H:i:s');
        $connection = $this->getConnection();
        $affectedRows = $connection->insertOnDuplicate(
            $this->getMainTable(),
            [
                'webhook_id' => $webhookId,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'status' => self::STATUS_PROCESSING,
                'received_at' => $now,
                'last_received_at' => $now,
                'processing_started_at' => $now,
                'duplicate_count' => 0,
            ],
            [
                'last_received_at' => new Zend_Db_Expr('CURRENT_TIMESTAMP'),
                'duplicate_count' => new Zend_Db_Expr('duplicate_count + 1'),
            ]
        );

        if ((int)$affectedRows === 1) {
            return self::CLAIM_NEW;
        }

        $select = $connection->select()
            ->from($this->getMainTable(), ['status', 'processing_started_at'])
            ->where('webhook_id = ?', $webhookId);
        $row = $connection->fetchRow($select);
        if (!$row || $row['status'] === self::STATUS_PROCESSED) {
            return self::CLAIM_PROCESSED;
        }

        $startedAt = strtotime($row['processing_started_at']);
        if ($startedAt !== false && $startedAt > time() - self::PROCESSING_LEASE) {
            return self::CLAIM_BUSY;
        }

        $updatedRows = $connection->update(
            $this->getMainTable(),
            [
                'processing_started_at' => $now,
                'last_received_at' => $now,
            ],
            [
                'webhook_id = ?' => $webhookId,
                'status = ?' => self::STATUS_PROCESSING,
                'processing_started_at < ?' => gmdate(
                    'Y-m-d H:i:s',
                    time() - self::PROCESSING_LEASE
                ),
            ]
        );

        return (int)$updatedRows === 1 ? self::CLAIM_NEW : self::CLAIM_BUSY;
    }

    /**
     * Mark a webhook as successfully processed.
     *
     * @param string $webhookId
     * @return bool
     */
    public function markProcessed($webhookId)
    {
        $updatedRows = $this->getConnection()->update(
            $this->getMainTable(),
            [
                'status' => self::STATUS_PROCESSED,
                'processed_at' => gmdate('Y-m-d H:i:s'),
            ],
            [
                'webhook_id = ?' => $webhookId,
                'status = ?' => self::STATUS_PROCESSING,
            ]
        );

        return (int)$updatedRows === 1;
    }

    /**
     * Release a failed webhook so that a provider retry can process it.
     *
     * @param string $webhookId
     * @return void
     */
    public function release($webhookId)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [
                'webhook_id = ?' => $webhookId,
                'status = ?' => self::STATUS_PROCESSING,
            ]
        );
    }
}
