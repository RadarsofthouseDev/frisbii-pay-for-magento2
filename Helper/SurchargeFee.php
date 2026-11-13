<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

class SurchargeFee extends AbstractHelper
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var Email
     */
    private $email;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * SurchargeFee constructor.
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Email $email
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        CartRepositoryInterface $quoteRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Email $email,
        Logger $logger,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->quoteRepository = $quoteRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->email = $email;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Get quote
     *
     * @param string $quoteId
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote($quoteId)
    {
        return $this->quoteRepository->get($quoteId);
    }

    /**
     * Get order
     *
     * @param string $incrementId
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder($incrementId)
    {
        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }

    /**
     * Update fee to order
     *
     * @param string $orderIncrementId
     * @param array $charge
     */
    public function updateFeeToOrder($orderIncrementId, $charge)
    {
        try {
            $order = $this->getOrder($orderIncrementId);
            // setting store to get correct prices and totals
            $this->storeManager->setCurrentStore($order->getStoreId());
            $quote = $this->getQuote($order->getQuoteId());
            if (array_key_exists('source', $charge) &&
                array_key_exists('surcharge_fee', $charge['source']) &&
                $charge['source']['surcharge_fee'] > 0
            ) {
                $surchargeFee = (float)($charge['source']['surcharge_fee'] / 100);
                $quote->setReepaySurchargeFee($surchargeFee);
                $quote->setTotalsCollectedFlag(false)->collectTotals();
                $quote->save();
                $order->setReepaySurchargeFee($surchargeFee)
                    ->setSubtotal($quote->getSubtotal())
                    ->setBaseSubtotal($quote->getBaseSubtotal())
                    ->setGrandTotal($quote->getGrandTotal())
                    ->setBaseGrandTotal($quote->getBaseGrandTotal());
                $order->save();
                $this->logger->addDebug('Applied surcharge fee to order.', [
                    'order_increment_id' => $orderIncrementId,
                    'surcharge_fee' => $surchargeFee
                ]);
            }
        } catch (\Exception $exception) {
            $this->logger->addError($exception->getMessage());
        }
        $this->email->sendEmail($orderIncrementId);
    }

    /**
     * Get Invoice is captured surcharge fee
     *
     * @param string $orderId
     * @return bool
     */
    public function isInvoicedSurchargeFee($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
        try {
            $invoiceObject = $this->invoiceRepository->getList($searchCriteria);
            $invoiceRecords = $invoiceObject->getItems();
            foreach ($invoiceRecords as $invoiceRecord) {
                if ($invoiceRecord->getReepaySurchargeFee() > 0) {
                    return true;
                }
            }
        } catch (LocalizedException $localizedException) {
            $this->logger->addError($localizedException->getMessage());
        }
        return false;
    }

    /**
     * Get available surcharge fee refund amount
     *
     * @param string $orderId
     * @return float
     */
    public function getAvailableSurchargeFeeRefundAmount($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
        try {
            $invoiceObject = $this->invoiceRepository->getList($searchCriteria);
            $invoiceRecords = $invoiceObject->getItems();
            $invoiced = 0;
            foreach ($invoiceRecords as $invoice) {
                if (!empty($invoice->getReepaySurchargeFee())) {
                    $invoiced += $invoice->getReepaySurchargeFee();
                }
            }

            $creditmemoObject = $this->creditmemoRepository->getList($searchCriteria);
            $creditmemoRecords = $creditmemoObject->getItems();
            $refunded = 0;
            foreach ($creditmemoRecords as $creditmemo) {
                if (!empty($creditmemo->getReepaySurchargeFee())) {
                    $refunded += $creditmemo->getReepaySurchargeFee();
                }
            }
            return round($invoiced - $refunded, 2);
        } catch (LocalizedException $localizedException) {
            $this->logger->addError($localizedException->getMessage());
        }
    }
}
